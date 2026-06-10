<template>
  <DashboardLayout title="">
    <div class="videollamada-container h-100 d-flex flex-column pb-4">
      
      <div class="mb-4">
        <v-btn variant="text" prepend-icon="mdi-chevron-left" class="text-none font-weight-bold text-grey-darken-3 px-0" to="/mis-reservas">
          Volver a mis reservas
        </v-btn>
      </div>

      <!-- Header de Sesión -->
      <v-card class="rounded-xl mb-4 elevation-1 border-card bg-surface">
        <v-card-text class="pa-4 d-flex align-center justify-space-between flex-wrap gap-4">
          <div class="d-flex align-center">
            <v-avatar color="secondary" size="56" class="mr-4">
              <v-img v-if="getAvatarUrl()" :src="getAvatarUrl()"></v-img>
              <span v-else class="text-h5 text-white font-weight-bold">{{ getInitials(otherPersonName) }}</span>
            </v-avatar>
            <div>
              <div class="text-h6 font-weight-bold text-grey-darken-3">{{ serviceName }} - {{ otherPersonName }}</div>
              <div class="d-flex align-center mt-1">
                <v-chip size="small" color="success" variant="flat" class="mr-2 px-3 font-weight-bold">
                  <v-icon start size="small">mdi-circle</v-icon>
                  En sesión
                </v-chip>
                <v-chip size="small" color="grey-darken-1" variant="outlined" class="font-weight-medium bg-white">
                  <v-icon start size="small">mdi-clock-outline</v-icon>
                  {{ formattedDuration }}
                </v-chip>
              </div>
            </div>
          </div>
          <div class="d-flex align-center">
            <v-btn icon="mdi-dots-vertical" variant="text" color="grey-darken-2"></v-btn>
          </div>
        </v-card-text>
      </v-card>

      <!-- Main Layout -->
      <v-row class="flex-grow-1 min-h-0" style="min-height: 0;">
        
        <!-- Área de Video Principal -->
        <v-col cols="12" :md="showChat ? 8 : 12" class="h-100 pb-0 transition-all">
          <v-card class="bg-grey-darken-4 rounded-xl overflow-hidden position-relative elevation-4 w-100 h-100 d-flex flex-column border-0">
            
            <!-- Video Remoto (o Local si estás solo) -->
            <div class="flex-grow-1 position-relative w-100" style="overflow: hidden; min-height: 0;">
              <video 
                v-if="remoteParticipant"
                :ref="el => setVideoRef(el, remoteParticipant.sid)" 
                autoplay 
                playsinline 
                class="w-100 h-100 video-cover" 
                :class="{ 'd-none': remoteParticipant.isCameraMuted }"
              ></video>
              <audio v-if="remoteParticipant" :ref="el => setAudioRef(el, remoteParticipant.sid)" autoplay></audio>
              
              <!-- Placeholder remoto -->
              <div v-if="!remoteParticipant || remoteParticipant.isCameraMuted" class="d-flex flex-column align-center justify-center w-100 h-100 position-absolute top-0 left-0 bg-grey-darken-3" style="z-index: 1;">
                <div v-if="!remoteParticipant" class="text-center d-flex flex-column align-center">
                  <v-progress-circular v-if="roomConnected" indeterminate color="primary" class="mb-4" size="50"></v-progress-circular>
                  <div class="text-white opacity-90 text-h6 px-4 text-center">Esperando al otro participante...</div>
                </div>
                <v-avatar v-else color="secondary" size="120">
                  <span class="text-h2 text-white font-weight-bold">{{ getInitials(remoteParticipant.name) }}</span>
                </v-avatar>
              </div>
              
              <!-- Etiqueta Nombre Remoto -->
              <div v-if="remoteParticipant" class="position-absolute top-0 left-0 ma-4">
                <v-chip color="rgba(0,0,0,0.6)" class="text-white font-weight-medium border" style="backdrop-filter: blur(4px);">
                  <v-icon :color="remoteParticipant.isMicMuted ? 'error' : 'success'" size="small" class="mr-2">
                    {{ remoteParticipant.isMicMuted ? 'mdi-microphone-off' : 'mdi-poll' }}
                  </v-icon>
                  {{ remoteParticipant.name }}
                </v-chip>
              </div>

              <!-- Ventana Flotante Local (PiP) -->
              <div class="pip-container elevation-6 rounded-lg overflow-hidden border" style="z-index: 20;">
                <video 
                  ref="localVideo" 
                  autoplay 
                  muted 
                  playsinline 
                  class="w-100 h-100 video-cover pip-video" 
                  :class="{ 'd-none': localData.isCameraMuted }"
                ></video>
                <div v-if="localData.isCameraMuted" class="d-flex align-center justify-center w-100 h-100 position-absolute top-0 left-0 bg-primary-darken-1">
                  <v-avatar color="primary" size="50">
                    <span class="text-h5 text-secondary font-weight-bold">{{ getInitials(localData.name) }}</span>
                  </v-avatar>
                </div>
                <div class="position-absolute bottom-0 left-0 pa-1 w-100" style="background: linear-gradient(transparent, rgba(0,0,0,0.7)); z-index: 21;">
                  <span class="text-caption text-white font-weight-medium px-1">Tú</span>
                </div>
              </div>
            </div>

            <!-- Toolbar Inferior -->
            <div class="bg-grey-darken-4 pa-4 d-flex justify-center align-center border-top flex-wrap" style="z-index: 30;">
              <div class="d-flex gap-4">
                <!-- Micrófono -->
                <div class="d-flex flex-column align-center mr-4">
                  <v-btn 
                    :color="localData.isMicMuted ? 'error' : 'secondary'" 
                    :icon="localData.isMicMuted ? 'mdi-microphone-off' : 'mdi-microphone'" 
                    @click="toggleMute" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  ></v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Micrófono</span>
                </div>
                
                <!-- Cámara -->
                <div class="d-flex flex-column align-center mr-4">
                  <v-btn 
                    :color="localData.isCameraMuted ? 'error' : 'secondary'" 
                    :icon="localData.isCameraMuted ? 'mdi-video-off' : 'mdi-video'" 
                    @click="toggleCamera" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  ></v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Cámara</span>
                </div>
                
                <!-- Compartir Pantalla -->
                <div class="d-flex flex-column align-center mr-4">
                  <v-btn 
                    color="secondary" 
                    icon="mdi-monitor-share" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  ></v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Pantalla</span>
                </div>
                
                <!-- Chat Toggle -->
                <div class="d-flex flex-column align-center mr-4">
                  <v-btn 
                    color="secondary" 
                    icon="mdi-message-text" 
                    @click="showChat = !showChat" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  >
                    <v-badge v-if="unreadCount > 0" :content="unreadCount" color="error">
                      <v-icon>mdi-message-text</v-icon>
                    </v-badge>
                    <v-icon v-else>mdi-message-text</v-icon>
                  </v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Chat</span>
                </div>
                
                <!-- Más opciones -->
                <div class="d-flex flex-column align-center mr-6">
                  <v-btn 
                    color="secondary" 
                    icon="mdi-dots-horizontal" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  ></v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Más opciones</span>
                </div>
                
                <!-- Finalizar -->
                <div class="d-flex flex-column align-center">
                  <v-btn 
                    color="error" 
                    icon="mdi-phone-hangup" 
                    @click="leaveRoom" 
                    size="large"
                    variant="flat"
                    class="mb-1 text-white"
                  ></v-btn>
                  <span class="text-caption text-white font-weight-medium opacity-80">Finalizar llamada</span>
                </div>
              </div>
            </div>

          </v-card>
        </v-col>

        <!-- Panel Lateral: Chat -->
        <v-col v-if="showChat" cols="12" md="4" class="h-100 pb-0">
          <v-card class="rounded-xl h-100 elevation-2 border-card d-flex flex-column bg-grey-lighten-5">
            <!-- Header Chat -->
            <div class="pa-4 d-flex justify-space-between align-center border-bottom bg-white">
              <span class="text-h6 font-weight-bold text-grey-darken-3">Chat</span>
              <v-btn icon="mdi-close" variant="text" size="small" @click="showChat = false"></v-btn>
            </div>
          
          <!-- Mensajes -->
          <div class="flex-grow-1 overflow-y-auto pa-4 bg-transparent d-flex flex-column" ref="chatContainer">
            <div v-if="messages.length === 0" class="text-center opacity-60 my-auto text-body-2">
              Envía un mensaje para iniciar el chat.<br/>
              <small>Los mensajes desaparecerán al finalizar la sesión.</small>
            </div>
            <div v-for="msg in messages" :key="msg.id" class="mb-4">
              <!-- Self Message -->
              <div v-if="msg.isSelf" class="d-flex justify-end">
                <div class="pa-3 rounded-xl rounded-tr-sm" style="background-color: #EBE5D9; max-width: 85%;">
                  <div class="text-caption font-weight-medium mb-1" style="color: #8C6D46;">
                    Tú <span class="ml-1 opacity-60">{{ msg.time }}</span>
                  </div>
                  <div class="text-body-2 text-grey-darken-4" style="line-height: 1.4;">
                    {{ msg.text }}
                  </div>
                </div>
              </div>
              
              <!-- Remote Message -->
              <div v-else class="d-flex align-start">
                <v-avatar color="secondary" size="36" class="mr-3 mt-1 elevation-1">
                  <v-img v-if="otherPersonAvatar" :src="otherPersonAvatar"></v-img>
                  <span v-else class="text-caption text-white font-weight-bold">{{ getInitials(msg.sender) }}</span>
                </v-avatar>
                <div style="max-width: 75%;">
                  <div class="d-flex align-baseline mb-1">
                    <span class="text-body-2 font-weight-bold text-grey-darken-3 mr-2">{{ msg.sender }}</span>
                    <span class="text-caption text-grey">{{ msg.time }}</span>
                  </div>
                  <div class="pa-3 rounded-xl rounded-tl-sm bg-white elevation-1">
                    <div class="text-body-2 text-grey-darken-4" style="line-height: 1.4;">
                      {{ msg.text }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Input Chat -->
          <div class="pa-2 bg-white border-top">
            <v-text-field
              v-model="newMessage"
              placeholder="Escribe un mensaje..."
              variant="plain"
              density="compact"
              hide-details
              class="px-2"
              @keyup.enter="sendMessage"
            >
              <template v-slot:append-inner>
                <v-btn icon="mdi-send" variant="text" color="grey-darken-1" size="small" @click="sendMessage" :disabled="!newMessage.trim()"></v-btn>
              </template>
            </v-text-field>
          </div>
        </v-card>
        </v-col>
      </v-row>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Room, RoomEvent, createLocalTracks, RemoteParticipant, Track, DataPacket_Kind } from 'livekit-client'
import DashboardLayout from '../components/DashboardLayout.vue'

interface RemoteParticipantData {
  sid: string;
  identity: string;
  name: string;
  isMicMuted: boolean;
  isCameraMuted: boolean;
  participant: RemoteParticipant;
}

interface ChatMessage {
  id: number;
  sender: string;
  text: string;
  time: string;
  isSelf: boolean;
}

const route = useRoute()
const router = useRouter()

const reservationId = Number(route.params.id)
const localVideo = ref<HTMLVideoElement | null>(null)
const chatContainer = ref<HTMLElement | null>(null)

// Datos UI
const serviceName = ref('Cargando...')
const otherPersonName = ref('Participante')
const otherPersonAvatar = ref(null)

const videoRefs = ref<Record<string, HTMLVideoElement>>({})
const audioRefs = ref<Record<string, HTMLAudioElement>>({})

const localData = ref({
  name: 'Cargando...',
  isMicMuted: false,
  isCameraMuted: false
})

const remoteParticipants = ref<RemoteParticipantData[]>([])
const room = ref<Room | null>(null)
const error = ref<string>('')
const roomConnected = ref(false)

// Chat & Layout
const showChat = ref(true)
const messages = ref<ChatMessage[]>([])
const newMessage = ref('')
const unreadCount = ref(0)

// Timer
const durationSeconds = ref(0)
let timerInterval: any = null

const formattedDuration = computed(() => {
  const h = Math.floor(durationSeconds.value / 3600)
  const m = Math.floor((durationSeconds.value % 3600) / 60)
  const s = durationSeconds.value % 60
  const pad = (n: number) => n.toString().padStart(2, '0')
  if (h > 0) return `${pad(h)}:${pad(m)}:${pad(s)}`
  return `${pad(m)}:${pad(s)}`
})

// En esta versión UI, mostramos solo al principal (el primero que entra o el que habla).
const remoteParticipant = computed(() => {
  return remoteParticipants.value.length > 0 ? remoteParticipants.value[0] : null
})

function getInitials(name: string) {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return name.substring(0, 2).toUpperCase()
}

function getAvatarUrl() {
  return otherPersonAvatar.value
}

function setVideoRef(el: any, sid: string) {
  if (el) videoRefs.value[sid] = el
}
function setAudioRef(el: any, sid: string) {
  if (el) audioRefs.value[sid] = el
}

function updateParticipantState(p: RemoteParticipant) {
  const index = remoteParticipants.value.findIndex(x => x.sid === p.sid)
  const data: RemoteParticipantData = {
    sid: p.sid,
    identity: p.identity,
    name: p.name || p.identity,
    isMicMuted: !p.isMicrophoneEnabled,
    isCameraMuted: !p.isCameraEnabled,
    participant: p
  }
  if (index >= 0) {
    remoteParticipants.value[index] = data
  } else {
    remoteParticipants.value.push(data)
  }
}

function removeParticipant(p: RemoteParticipant) {
  remoteParticipants.value = remoteParticipants.value.filter(x => x.sid !== p.sid)
  delete videoRefs.value[p.sid]
  delete audioRefs.value[p.sid]
}

const encoder = new TextEncoder()
const decoder = new TextDecoder()

async function initCall() {
  try {
    // 1. Obtener detalles de la reserva para el UI
    const resReserva = await fetch(`/api/reservas`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    if (resReserva.ok) {
      const data = await resReserva.json()
      const reserva = (data.data || []).find((r: any) => r.id === reservationId)
      if (reserva) {
        serviceName.value = reserva.servicio?.nombre || 'Sesión Remota'
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const isClienteLocal = user.role !== 'profesional'
        
        if (isClienteLocal) {
          const p = reserva.servicio?.profesional?.usuario
          otherPersonName.value = p ? `${p.nombre || ''} ${p.apellido || ''}`.trim() : 'Profesional'
          otherPersonAvatar.value = reserva.servicio?.profesional?.foto_perfil_url
        } else {
          const c = reserva.cliente?.usuario
          otherPersonName.value = c ? `${c.nombre || ''} ${c.apellido || ''}`.trim() : 'Cliente'
        }
      }
    }

    // 2. Obtener Token
    const resp = await fetch(`/api/reservas/${reservationId}/videollamada/token`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    })
    if (!resp.ok) throw new Error('No se pudo obtener el token de la videollamada')
    const tokenData = await resp.json()
    const { url, token, name } = tokenData
    
    localData.value.name = name || 'Tú'

    // Connect to LiveKit room
    const lkRoom = new Room()
    
    lkRoom.on(RoomEvent.ParticipantConnected, updateParticipantState)
    lkRoom.on(RoomEvent.ParticipantDisconnected, removeParticipant)
    
    lkRoom.on(RoomEvent.TrackSubscribed, async (track, publication, participant) => {
      updateParticipantState(participant)
      await nextTick()
      
      if (track.kind === Track.Kind.Video) {
        const el = videoRefs.value[participant.sid]
        if (el) track.attach(el)
      } else if (track.kind === Track.Kind.Audio) {
        const el = audioRefs.value[participant.sid]
        if (el) track.attach(el)
      }
    })

    lkRoom.on(RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
      track.detach()
      updateParticipantState(participant)
    })

    lkRoom.on(RoomEvent.TrackMuted, (pub, participant) => {
      if (participant && participant.identity !== lkRoom.localParticipant.identity) {
        updateParticipantState(participant as RemoteParticipant)
      }
    })
    lkRoom.on(RoomEvent.TrackUnmuted, (pub, participant) => {
      if (participant && participant.identity !== lkRoom.localParticipant.identity) {
        updateParticipantState(participant as RemoteParticipant)
      }
    })

    // Data Channel para Chat
    lkRoom.on(RoomEvent.DataReceived, (payload, participant) => {
      const text = decoder.decode(payload)
      messages.value.push({
        id: Date.now(),
        sender: participant?.name || participant?.identity || 'Participante',
        text,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        isSelf: false
      })
      if (!showChat.value) {
        unreadCount.value++
      }
      scrollToBottom()
    })

    // Connect
    await lkRoom.connect(url, token, { autoSubscribe: true })
    room.value = lkRoom
    roomConnected.value = true
    
    // Timer
    timerInterval = setInterval(() => { durationSeconds.value++ }, 1000)
    
    // Add already connected participants
    lkRoom.remoteParticipants.forEach(updateParticipantState)

    // Create local tracks and publish
    const tracks = await createLocalTracks({ audio: true, video: true })
    for (const t of tracks) {
      await lkRoom.localParticipant.publishTrack(t)
    }
    
    // Attach local video
    const videoTrack = tracks.find(t => t.kind === Track.Kind.Video)
    if (videoTrack) {
      await nextTick()
      if (localVideo.value) {
        videoTrack.attach(localVideo.value)
      }
    }

  } catch (e: any) {
    error.value = e.message || 'Error inesperado'
  }
}

function leaveRoom() {
  if (room.value) {
    room.value.disconnect()
    room.value = null
    roomConnected.value = false
    router.push(`/mis-reservas?calificar=${reservationId}`)
  }
}

function toggleMute() {
  if (room.value) {
    localData.value.isMicMuted = !localData.value.isMicMuted
    room.value.localParticipant.setMicrophoneEnabled(!localData.value.isMicMuted)
  }
}

function toggleCamera() {
  if (room.value) {
    localData.value.isCameraMuted = !localData.value.isCameraMuted
    room.value.localParticipant.setCameraEnabled(!localData.value.isCameraMuted)
  }
}

function sendMessage() {
  if (!newMessage.value.trim() || !room.value) return;
  const text = newMessage.value.trim()
  const data = encoder.encode(text)
  room.value.localParticipant.publishData(data, DataPacket_Kind.RELIABLE)
  
  messages.value.push({
    id: Date.now(),
    sender: 'Tú',
    text,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isSelf: true
  })
  newMessage.value = ''
  scrollToBottom()
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}

// Limpiar unread
import { watch } from 'vue'
watch(showChat, (val) => {
  if (val) unreadCount.value = 0
})

onMounted(() => {
  if (isNaN(reservationId)) {
    error.value = 'ID de reserva inválido'
    return
  }
  initCall()
})

onBeforeUnmount(() => {
  if (room.value) room.value.disconnect()
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<style scoped>
.videollamada-container {
  height: calc(100vh - 120px);
  overflow: hidden;
}

.min-h-0 {
  min-height: 0;
  max-height: 100%;
}

.border-card {
  border: 1px solid rgba(140, 109, 70, 0.1);
}

.border-top {
  border-top: 1px solid rgba(255,255,255,0.1);
}

.border-bottom {
  border-bottom: 1px solid rgba(140, 109, 70, 0.1);
}

.transition-all {
  transition: all 0.3s ease;
}

.video-cover {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Fill the whole area for a better native look */
  background-color: #212121;
}

/* Picture in Picture styling */
.pip-container {
  position: absolute;
  bottom: 24px;
  right: 24px;
  width: 240px;
  height: 150px;
  background-color: #000;
  z-index: 10;
  border-color: rgba(255,255,255,0.2) !important;
}

.pip-video {
  transform: scaleX(-1); /* Espejo para la cámara local */
}

/* Scrollbar para el chat */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(140, 109, 70, 0.2);
  border-radius: 4px;
}
</style>
