<template>
  <v-container class="fill-height bg-grey-darken-4" fluid>
    <v-row justify="center" align="start" style="width: 100%;">
      <v-col cols="12" class="text-center">
        <h1 class="text-h4 font-weight-bold mb-4 text-white">Videollamada</h1>
        <v-alert v-if="error" type="error" class="mb-4" closable @click:close="error = ''">
          {{ error }}
        </v-alert>
      </v-col>

      <!-- Video Grid -->
      <v-col cols="12" md="10" lg="8">
        <v-row>
          <!-- Local Video -->
          <v-col cols="12" sm="6" v-if="localTrack">
            <v-card class="bg-black rounded-lg overflow-hidden position-relative elevation-10">
              <video ref="localVideo" autoplay muted playsinline></video>
              <div class="position-absolute top-0 left-0 ma-2">
                <v-chip color="black" size="small" style="opacity: 0.7;">Tú</v-chip>
              </div>
            </v-card>
          </v-col>

          <!-- Remote Videos -->
          <v-col cols="12" sm="6" v-for="(track, idx) in remoteTracks" :key="idx">
            <v-card class="bg-black rounded-lg overflow-hidden position-relative elevation-10">
              <video :ref="setRemoteVideoRef" autoplay playsinline></video>
              <div class="position-absolute top-0 left-0 ma-2">
                <v-chip color="black" size="small" style="opacity: 0.7;">Participante</v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" class="text-center mt-6 d-flex justify-center align-center">
        <v-btn v-if="roomConnected" :color="isMuted ? 'error' : 'grey-darken-2'" :icon="isMuted ? 'mdi-microphone-off' : 'mdi-microphone'" @click="toggleMute" class="mx-2" elevation="5"></v-btn>
        <v-btn v-if="roomConnected" :color="isCameraOff ? 'error' : 'grey-darken-2'" :icon="isCameraOff ? 'mdi-video-off' : 'mdi-video'" @click="toggleCamera" class="mx-2" elevation="5"></v-btn>
        <v-btn v-if="roomConnected" color="error" size="large" prepend-icon="mdi-phone-hangup" @click="leaveRoom" elevation="5" class="mx-2">
          Salir
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Room, RoomEvent, createLocalTracks } from 'livekit-client'
import type { Participant, RemoteTrackPublication } from 'livekit-client'

const route = useRoute()
const router = useRouter()

const reservationId = Number(route.params.id)
const localVideo = ref<HTMLVideoElement | null>(null)
const remoteVideoRefs = ref<HTMLVideoElement[]>([])

const localTrack = ref<any>(null)
const remoteTracks = ref<any[]>([])
const room = ref<Room | null>(null)
const error = ref<string>('')
const roomConnected = ref(false)

const isMuted = ref(false)
const isCameraOff = ref(false)

function setRemoteVideoRef(el: HTMLVideoElement | null) {
  if (el && !remoteVideoRefs.value.includes(el)) {
    remoteVideoRefs.value.push(el)
  }
}

async function initCall() {
  try {
    const resp = await fetch(`http://localhost:8000/api/reservas/${reservationId}/videollamada/token`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    if (!resp.ok) throw new Error('No se pudo obtener el token de la videollamada')
    const data = await resp.json()
    const { url, token } = data

    // Connect to LiveKit room
    const lkRoom = new Room()
    await lkRoom.connect(url, token, { autoSubscribe: true })
    room.value = lkRoom
    roomConnected.value = true

    // Create local video/audio tracks
    const tracks = await createLocalTracks({ audio: true, video: true })
    for (const t of tracks) {
      await lkRoom.localParticipant.publishTrack(t)
    }
    
    // Attach local video
    const videoTrack = tracks.find(t => t.kind === 'video')
    if (videoTrack) {
      localTrack.value = videoTrack
      await nextTick()
      if (localVideo.value) {
        videoTrack.attach(localVideo.value)
      }
    }

    // Listen for remote participants' tracks
    lkRoom.on(RoomEvent.TrackSubscribed, async (track, publication, participant) => {
      remoteTracks.value.push(track)
      await nextTick()
      const el = remoteVideoRefs.value[remoteTracks.value.length - 1]
      if (el) track.attach(el)
    })
    
    lkRoom.on(RoomEvent.TrackUnsubscribed, (track) => {
      if (track) track.detach()
      remoteTracks.value = remoteTracks.value.filter(t => t.sid !== track.sid)
      remoteVideoRefs.value = [] // Force re-render of refs
    })

  } catch (e: any) {
    error.value = e.message || 'Error inesperado'
  }
}

function leaveRoom() {
  if (room.value) {
    room.value.disconnect()
    room.value = null
    roomConnected.value = false
    router.back()
  }
}

function toggleMute() {
  if (room.value) {
    isMuted.value = !isMuted.value
    room.value.localParticipant.setMicrophoneEnabled(!isMuted.value)
  }
}

function toggleCamera() {
  if (room.value) {
    isCameraOff.value = !isCameraOff.value
    room.value.localParticipant.setCameraEnabled(!isCameraOff.value)
  }
}

onMounted(() => {
  if (isNaN(reservationId)) {
    error.value = 'ID de reserva inválido'
    return
  }
  initCall()
})

onBeforeUnmount(() => {
  if (room.value) room.value.disconnect()
})
</script>

<style scoped>
video {
  width: 100%;
  aspect-ratio: 16 / 9;
  background-color: #000;
  object-fit: cover;
  display: block;
}
</style>
