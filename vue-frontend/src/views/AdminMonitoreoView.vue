<template>
  <DashboardLayout title="Métricas y Monitoreo">
    <!-- Header visual -->
    <v-row class="mb-6">
      <v-col cols="12">
        <BannerHeader
          title="Monitoreo del Sistema"
          subtitle="Visualiza las métricas globales del sistema en tiempo real. Analiza la actividad de reservas, ingresos de cobros, reputación de profesionales y estadísticas demográficas."
          icon="mdi-chart-timeline-variant"
        />
      </v-col>
    </v-row>

    <!-- Tabs selector -->
    <v-tabs v-model="activeTab" bg-color="transparent" color="primary" class="mb-6 border-b" align-tabs="start">
      <v-tab value="metricas" class="text-none font-weight-bold">
        <v-icon start>mdi-chart-bar</v-icon>
        Métricas Globales
      </v-tab>
      <v-tab value="logs" class="text-none font-weight-bold">
        <v-icon start>mdi-format-list-bulleted-type</v-icon>
        Logs de Actividad (NoSQL)
      </v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <v-window-item value="metricas">
        <!-- ── Top Action Bar ──────────────────────────────────────────── -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Última actualización: {{ lastUpdated }}
        </p>
      </div>
      <v-btn
        :loading="loading"
        color="primary"
        variant="tonal"
        prepend-icon="mdi-refresh"
        @click="fetchStats"
        class="text-none font-weight-bold"
      >
        Actualizar métricas
      </v-btn>
    </div>

    <!-- ── Loading Skeleton ─────────────────────────────────────────── -->
    <template v-if="loading">
      <v-row class="mb-4">
        <v-col v-for="i in 6" :key="i" cols="12" sm="6" md="4" lg="2">
          <v-skeleton-loader type="card" class="rounded-xl" />
        </v-col>
      </v-row>
      <v-row>
        <v-col v-for="i in 3" :key="i" cols="12" md="4">
          <v-skeleton-loader type="card, list-item-three-line" class="rounded-xl" />
        </v-col>
      </v-row>
    </template>

    <template v-else>
      <!-- ── KPI Cards ───────────────────────────────────────────────── -->
      <v-row class="mb-6">
        <!-- Total Usuarios -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#3B82F6')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Usuarios</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-account-group</v-icon>
            </div>
            <div class="text-h4 font-weight-bold text-white">{{ stats.total_usuarios ?? 0 }}</div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon> {{ stats.usuarios_activos ?? 0 }} activos
            </div>
          </v-card>
        </v-col>

        <!-- Servicios Activos -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#8B5CF6')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Servicios</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-briefcase-check</v-icon>
            </div>
            <div class="text-h4 font-weight-bold text-white">{{ stats.servicios_activos ?? 0 }}</div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon> de {{ stats.total_servicios ?? 0 }} totales
            </div>
          </v-card>
        </v-col>

        <!-- Total Reservas -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#10B981')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Reservas</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-calendar-check</v-icon>
            </div>
            <div class="text-h4 font-weight-bold text-white">{{ stats.total_reservas ?? 0 }}</div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon> {{ stats.reservas_finalizadas ?? 0 }} finalizadas
            </div>
          </v-card>
        </v-col>

        <!-- Ingresos Totales -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#F59E0B')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Ingresos</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-cash-multiple</v-icon>
            </div>
            <div class="text-h5 font-weight-bold text-white">{{ formatCurrency(stats.ingresos_totales) }}</div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon> pagos completados
            </div>
          </v-card>
        </v-col>

        <!-- Promedio Calificación -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#EF4444')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Calificación</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-star</v-icon>
            </div>
            <div class="text-h4 font-weight-bold text-white">
              {{ stats.promedio_calificacion != null ? stats.promedio_calificacion : 'N/A' }}
            </div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon> {{ stats.total_calificaciones ?? 0 }} reseñas
            </div>
          </v-card>
        </v-col>

        <!-- Reservas Canceladas -->
        <v-col cols="12" sm="6" md="4" lg="2">
          <v-card class="pa-5 rounded-xl elevation-1 card-hover h-100" :style="gradientStyle('#64748B')">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-white opacity-80 font-weight-medium text-uppercase">Canceladas</span>
              <v-icon color="white" size="22" class="opacity-80">mdi-calendar-remove</v-icon>
            </div>
            <div class="text-h4 font-weight-bold text-white">{{ stats.reservas_canceladas ?? 0 }}</div>
            <div class="text-caption text-white opacity-70 mt-1">
              <v-icon size="12">mdi-circle-small</v-icon>
              {{ totalReservas > 0 ? ((stats.reservas_canceladas / totalReservas) * 100).toFixed(1) + '%' : '0%' }} del total
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- ── Row 2: Gráfico Reservas por Mes + Distribución Usuarios + Distribución Reservas ── -->
      <v-row class="mb-6">
        <!-- Reservas por Mes (Bar chart) -->
        <v-col cols="12" md="8">
          <v-card class="pa-6 rounded-xl elevation-1 h-100">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h3 class="text-h6 font-weight-bold text-grey-darken-4">Reservas por Día</h3>
                <p class="text-caption text-medium-emphasis mb-0">Últimos 2 días</p>
              </div>
              <v-icon color="primary">mdi-chart-bar</v-icon>
            </div>
            <div v-if="!reservasPorDiaLabels.length" class="empty-state">
              <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-chart-bar</v-icon>
              <p class="text-body-2 text-medium-emphasis">No hay datos de reservas diarias todavía</p>
            </div>
            <canvas v-else ref="barChartRef" height="120"></canvas>
          </v-card>
        </v-col>

        <!-- Usuarios por Rol (Doughnut chart) -->
        <v-col cols="12" md="4">
          <v-card class="pa-6 rounded-xl elevation-1 h-100">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h3 class="text-h6 font-weight-bold text-grey-darken-4">Usuarios por Rol</h3>
                <p class="text-caption text-medium-emphasis mb-0">Distribución</p>
              </div>
              <v-icon color="primary">mdi-chart-donut</v-icon>
            </div>
            <div v-if="!Object.keys(stats.usuarios_por_rol || {}).length" class="empty-state">
              <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-account-question</v-icon>
              <p class="text-body-2 text-medium-emphasis">Aún no hay usuarios registrados</p>
            </div>
            <div v-else>
              <canvas ref="doughnutChartRef" height="160"></canvas>
              <div class="mt-4">
                <div v-for="(count, role) in stats.usuarios_por_rol" :key="role"
                  class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center">
                    <span class="role-dot mr-2" :style="{ background: getRoleColor(role) }"></span>
                    <span class="text-body-2 text-capitalize">{{ getRoleLabel(role) }}</span>
                  </div>
                  <v-chip :color="getRoleColorName(role)" size="x-small" variant="tonal" class="font-weight-bold">
                    {{ count }}
                  </v-chip>
                </div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- ── Row 3: Top Servicios + Estados de Reservas + Calificaciones ── -->
      <v-row class="mb-6">
        <!-- Top 5 Servicios -->
        <v-col cols="12" md="5">
          <v-card class="pa-6 rounded-xl elevation-1 h-100">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h3 class="text-h6 font-weight-bold text-grey-darken-4">Top 5 Servicios</h3>
                <p class="text-caption text-medium-emphasis mb-0">Por cantidad de reservas</p>
              </div>
              <v-icon color="primary">mdi-podium</v-icon>
            </div>
            <div v-if="!(stats.top_servicios || []).length" class="empty-state">
              <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-briefcase-outline</v-icon>
              <p class="text-body-2 text-medium-emphasis">Aún no hay servicios con reservas</p>
            </div>
            <div v-else>
              <div v-for="(s, i) in stats.top_servicios" :key="i" class="mb-4">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center">
                    <span class="rank-badge mr-2" :class="`rank-${i + 1}`">{{ i + 1 }}</span>
                    <span class="text-body-2 font-weight-medium" style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">{{ s.nombre }}</span>
                  </div>
                  <span class="text-caption font-weight-bold text-primary">{{ s.total_reservas }} reservas</span>
                </div>
                <v-progress-linear
                  :model-value="maxTopService > 0 ? (s.total_reservas / maxTopService) * 100 : 0"
                  color="primary"
                  height="6"
                  rounded
                  bg-color="grey-lighten-3"
                />
              </div>
            </div>
          </v-card>
        </v-col>

        <!-- Reservas por Estado -->
        <v-col cols="12" md="4">
          <v-card class="pa-6 rounded-xl elevation-1 h-100">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h3 class="text-h6 font-weight-bold text-grey-darken-4">Estado de Reservas</h3>
                <p class="text-caption text-medium-emphasis mb-0">Distribución actual</p>
              </div>
              <v-icon color="primary">mdi-chart-pie</v-icon>
            </div>
            <div v-if="!Object.keys(stats.reservas_por_estado || {}).length" class="empty-state">
              <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-calendar-blank</v-icon>
              <p class="text-body-2 text-medium-emphasis">Aún no hay reservas registradas</p>
            </div>
            <v-list v-else density="compact" class="pa-0">
              <v-list-item v-for="(count, estado) in stats.reservas_por_estado" :key="estado" class="px-0 rounded-lg mb-1 hover-item">
                <template v-slot:prepend>
                  <v-avatar :color="getEstadoColor(estado)" size="34" variant="tonal" class="mr-3">
                    <v-icon size="16">{{ getEstadoIcon(estado) }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-medium text-capitalize text-body-2">{{ estado }}</v-list-item-title>
                <template v-slot:append>
                  <div class="d-flex align-center">
                    <v-progress-linear
                      :model-value="totalReservas > 0 ? (count / totalReservas) * 100 : 0"
                      :color="getEstadoColor(estado)"
                      height="5"
                      rounded
                      style="width:60px"
                      class="mr-2"
                    />
                    <v-chip :color="getEstadoColor(estado)" size="x-small" variant="flat" class="font-weight-bold">{{ count }}</v-chip>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-card>
        </v-col>

        <!-- Calificaciones -->
        <v-col cols="12" md="3">
          <v-card class="pa-6 rounded-xl elevation-1 h-100">
            <div class="d-flex align-center justify-space-between mb-4">
              <div>
                <h3 class="text-h6 font-weight-bold text-grey-darken-4">Calificaciones</h3>
                <p class="text-caption text-medium-emphasis mb-0">Distribución de estrellas</p>
              </div>
              <v-icon color="amber">mdi-star</v-icon>
            </div>
            <!-- Promedio Grande -->
            <div class="text-center mb-4" v-if="stats.promedio_calificacion != null">
              <div class="text-h2 font-weight-black text-amber-darken-2">{{ stats.promedio_calificacion }}</div>
              <div class="d-flex justify-center mb-1">
                <v-icon v-for="n in 5" :key="n"
                  :color="n <= Math.round(stats.promedio_calificacion) ? 'amber' : 'grey-lighten-2'"
                  size="18">mdi-star</v-icon>
              </div>
              <span class="text-caption text-medium-emphasis">{{ stats.total_calificaciones }} reseñas</span>
            </div>
            <div v-else class="text-center mb-4">
              <v-icon size="48" color="grey-lighten-2" class="mb-2">mdi-star-off-outline</v-icon>
              <p class="text-caption text-medium-emphasis">Aún no hay calificaciones</p>
            </div>
            <!-- Barras por estrella -->
            <div v-for="n in [5,4,3,2,1]" :key="n" class="d-flex align-center mb-1">
              <span class="text-caption mr-1" style="width:8px">{{ n }}</span>
              <v-icon size="12" color="amber" class="mr-1">mdi-star</v-icon>
              <v-progress-linear
                :model-value="totalCalificaciones > 0 ? ((stats.calificaciones_por_puntuacion?.[n] || 0) / totalCalificaciones) * 100 : 0"
                color="amber"
                height="6"
                rounded
                bg-color="grey-lighten-3"
                class="flex-grow-1 mr-1"
              />
              <span class="text-caption text-medium-emphasis" style="width:20px; text-align:right">{{ stats.calificaciones_por_puntuacion?.[n] || 0 }}</span>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <!-- ── Row 4: Últimas Reservas ─────────────────────────────────── -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="rounded-xl elevation-1">
            <v-card-title class="pa-6 pb-2">
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <v-icon class="mr-2" color="primary">mdi-history</v-icon>
                  <span class="text-h6 font-weight-bold">Últimas Reservas</span>
                </div>
                <v-chip color="primary" size="small" variant="tonal">{{ (stats.ultimas_reservas || []).length }} recientes</v-chip>
              </div>
            </v-card-title>
            <v-card-text v-if="!(stats.ultimas_reservas || []).length" class="pa-0">
              <div class="empty-state py-8">
                <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-calendar-blank-outline</v-icon>
                <p class="text-body-2 text-medium-emphasis">No hay reservas recientes para mostrar</p>
              </div>
            </v-card-text>
            <v-data-table
              v-else
              :headers="reservaHeaders"
              :items="stats.ultimas_reservas || []"
              density="comfortable"
              class="rounded-xl"
              :items-per-page="10"
              hover
            >
              <template v-slot:item.estado="{ item }">
                <v-chip :color="getEstadoColor(item.estado)" size="small" variant="flat" class="font-weight-bold text-capitalize">
                  <v-icon start size="14">{{ getEstadoIcon(item.estado) }}</v-icon>
                  {{ item.estado }}
                </v-chip>
              </template>
              <template v-slot:item.fecha_hora_inicio="{ item }">
                <span class="text-body-2">{{ formatDateTime(item.fecha_hora_inicio) }}</span>
              </template>
              <template v-slot:item.created_at="{ item }">
                <span class="text-body-2 text-medium-emphasis">{{ formatDateTime(item.created_at) }}</span>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- ── Row 5: Últimos Usuarios ─────────────────────────────────── -->
      <v-row>
        <v-col cols="12">
          <v-card class="rounded-xl elevation-1">
            <v-card-title class="pa-6 pb-2">
              <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <v-icon class="mr-2" color="primary">mdi-account-plus</v-icon>
                  <span class="text-h6 font-weight-bold">Últimos Usuarios Registrados</span>
                </div>
                <v-chip color="primary" size="small" variant="tonal">{{ (stats.ultimos_usuarios || []).length }} recientes</v-chip>
              </div>
            </v-card-title>
            <v-card-text v-if="!(stats.ultimos_usuarios || []).length" class="pa-0">
              <div class="empty-state py-8">
                <v-icon size="48" color="grey-lighten-2" class="mb-3">mdi-account-question-outline</v-icon>
                <p class="text-body-2 text-medium-emphasis">No hay usuarios registrados recientemente</p>
              </div>
            </v-card-text>
            <v-data-table
              v-else
              :headers="userHeaders"
              :items="stats.ultimos_usuarios || []"
              density="comfortable"
              class="rounded-xl"
              :items-per-page="10"
              hover
            >
              <template v-slot:item.nombre="{ item }">
                <div class="d-flex align-center">
                  <v-avatar :color="getRoleColorName(item.role)" size="30" variant="tonal" class="mr-2">
                    <span class="text-caption font-weight-bold">{{ item.nombre?.substring(0,2).toUpperCase() }}</span>
                  </v-avatar>
                  <span class="text-body-2 font-weight-medium">{{ item.nombre }}</span>
                </div>
              </template>
              <template v-slot:item.role="{ item }">
                <v-chip :color="getRoleColorName(item.role)" size="small" variant="flat" class="font-weight-bold text-capitalize">
                  {{ getRoleLabel(item.role) }}
                </v-chip>
              </template>
              <template v-slot:item.activo="{ item }">
                <v-chip :color="item.activo ? 'success' : 'error'" size="small" variant="tonal">
                  <v-icon start size="12">{{ item.activo ? 'mdi-check-circle' : 'mdi-close-circle' }}</v-icon>
                  {{ item.activo ? 'Activo' : 'Inactivo' }}
                </v-chip>
              </template>
              <template v-slot:item.fecha_registro="{ item }">
                <span class="text-body-2 text-medium-emphasis">{{ formatDateTime(item.fecha_registro) }}</span>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </template>
      </v-window-item>

      <v-window-item value="logs">
        <!-- ── Top Action Bar Logs ─────────────────────────────────────── -->
        <v-card class="pa-6 rounded-xl elevation-1 mb-6 border-card bg-surface">
          <v-row class="align-center">
            <!-- Search bar -->
            <v-col cols="12" md="6" lg="5">
              <v-text-field
                v-model="searchLog"
                placeholder="Buscar por acción, IP, detalles..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                hide-details
                color="primary"
                bg-color="white"
              ></v-text-field>
            </v-col>
            <!-- Severity Filter -->
            <v-col cols="12" sm="6" md="3" lg="3">
              <v-select
                v-model="logsFilterType"
                :items="[
                  { title: 'Todos los niveles', value: 'todos' },
                  { title: 'Info', value: 'info' },
                  { title: 'Warning', value: 'warning' },
                  { title: 'Error', value: 'error' },
                  { title: 'Critical', value: 'critical' }
                ]"
                label="Nivel de severidad"
                variant="outlined"
                density="comfortable"
                hide-details
                color="primary"
                bg-color="white"
              ></v-select>
            </v-col>
            <v-spacer class="d-none d-sm-flex"></v-spacer>
            <!-- Action buttons -->
            <v-col cols="auto">
              <v-btn
                :loading="loadingLogs"
                color="primary"
                variant="tonal"
                prepend-icon="mdi-refresh"
                @click="fetchLogs"
                class="text-none font-weight-bold"
              >
                Actualizar logs
              </v-btn>
            </v-col>
          </v-row>
        </v-card>

        <!-- Logs Data Table -->
        <v-card class="rounded-xl elevation-1 border-card">
          <v-card-title class="pa-6 pb-2 d-flex justify-space-between align-center">
            <span class="text-h6 font-weight-bold">Registros de Actividad Recientes</span>
            <v-chip color="primary" size="small" variant="tonal" v-if="filteredLogs.length > 0">
              {{ filteredLogs.length }} encontrados
            </v-chip>
          </v-card-title>
          <v-data-table
            :headers="logHeaders"
            :items="filteredLogs"
            :loading="loadingLogs"
            density="comfortable"
            class="rounded-xl"
            :items-per-page="10"
            hover
          >
            <!-- Date/Time Formatting -->
            <template v-slot:item.timestamp="{ item }">
              <span class="text-body-2 font-weight-medium text-grey-darken-3">
                {{ formatDateTime(item.timestamp) }}
              </span>
            </template>
            <!-- Severity Type Chip -->
            <template v-slot:item.tipo="{ item }">
              <v-chip :color="getLogTypeColor(item.tipo)" size="small" variant="flat" class="font-weight-bold text-uppercase">
                <v-icon start size="14">{{ getLogTypeIcon(item.tipo) }}</v-icon>
                {{ item.tipo }}
              </v-chip>
            </template>
            <!-- Action style -->
            <template v-slot:item.accion="{ item }">
              <span class="text-body-2 font-weight-bold text-grey-darken-4">{{ item.accion }}</span>
            </template>
            <!-- Action view details button -->
            <template v-slot:item.actions="{ item }">
              <v-btn
                size="small"
                variant="text"
                color="primary"
                prepend-icon="mdi-eye-outline"
                class="text-none font-weight-bold"
                @click="openLogDetails(item)"
              >
                Ver más
              </v-btn>
            </template>
            <!-- Empty state -->
            <template v-slot:no-data>
              <div class="empty-state py-8 text-center">
                <v-icon size="48" color="grey-lighten-1" class="mb-3">mdi-text-box-search-outline</v-icon>
                <p class="text-body-2 text-medium-emphasis">No se encontraron logs de actividad</p>
              </div>
            </template>
          </v-data-table>
        </v-card>

        <!-- Log Details Dialog -->
        <v-dialog v-model="dialogLogDetails" max-width="600" scrollable>
          <v-card class="rounded-xl border-card elevation-4">
            <v-card-title class="pa-5 bg-grey-lighten-4 d-flex align-center justify-space-between">
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-clipboard-text-search-outline</v-icon>
                <span class="text-h6 font-weight-bold">Detalles de Actividad</span>
              </div>
              <v-btn icon="mdi-close" variant="text" size="small" @click="dialogLogDetails = false"></v-btn>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-5" style="max-height: 450px;">
              <v-row v-if="selectedLog">
                <v-col cols="12">
                  <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Acción</div>
                  <div class="text-body-1 font-weight-bold text-grey-darken-4 mb-4">{{ selectedLog.accion }}</div>
                  
                  <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Detalles Técnicos</div>
                  <v-sheet border rounded class="pa-4 bg-grey-lighten-5 mb-4 text-mono text-body-2 overflow-x-auto" style="font-family: monospace;">
                    <pre>{{ JSON.stringify(selectedLog.detalles, null, 2) }}</pre>
                  </v-sheet>

                  <v-row>
                    <v-col cols="6">
                      <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Nivel / Severidad</div>
                      <v-chip :color="getLogTypeColor(selectedLog.tipo)" size="small" variant="flat" class="font-weight-bold text-uppercase">
                        {{ selectedLog.tipo }}
                      </v-chip>
                    </v-col>
                    <v-col cols="6">
                      <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Fecha / Hora</div>
                      <div class="text-body-2 text-grey-darken-3">{{ formatDateTime(selectedLog.timestamp) }}</div>
                    </v-col>
                    <v-col cols="6">
                      <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Usuario ID</div>
                      <div class="text-body-2 text-grey-darken-3">{{ selectedLog.usuario_id || 'Invitado' }}</div>
                    </v-col>
                    <v-col cols="6">
                      <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Dirección IP</div>
                      <div class="text-body-2 text-grey-darken-3">{{ selectedLog.ip }}</div>
                    </v-col>
                    <v-col cols="12">
                      <div class="text-caption text-uppercase font-weight-bold text-grey-darken-1 mb-1">Navegador (User Agent)</div>
                      <div class="text-body-2 text-grey-darken-3 text-wrap">{{ selectedLog.user_agent }}</div>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions class="pa-4 bg-grey-lighten-4 justify-end">
              <v-btn color="secondary" variant="flat" class="text-none font-weight-bold px-6 rounded-lg text-white" @click="dialogLogDetails = false">
                Cerrar
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-window-item>
    </v-window>

  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import Chart from 'chart.js/auto'
import DashboardLayout from '../components/DashboardLayout.vue'
import BannerHeader from '../components/BannerHeader.vue'
import { useAuth } from '../composables/useAuth'
import { useDateFormatter } from '../composables/useDateFormatter'
import { useReservationStatus } from '../composables/useReservationStatus'

const { getAuthHeaders } = useAuth()
const { formatDateTime } = useDateFormatter()
const { getEstadoColor, getEstadoIcon } = useReservationStatus()

// Chart.js statically imported at the top

// ── State ─────────────────────────────────────────────────────────────────────
const loading     = ref(true)
const lastUpdated = ref('—')

// Logs State
const activeTab = ref('metricas')
const loadingLogs = ref(false)
const searchLog = ref('')
const logsFilterType = ref('todos')
const logs = ref([])
const dialogLogDetails = ref(false)
const selectedLog = ref(null)

const logHeaders = [
  { title: 'Fecha/Hora', key: 'timestamp', width: '180px' },
  { title: 'Acción', key: 'accion' },
  { title: 'Tipo', key: 'tipo', width: '110px' },
  { title: 'Usuario ID', key: 'usuario_id', width: '110px' },
  { title: 'IP', key: 'ip', width: '130px' },
  { title: 'Detalles', key: 'actions', sortable: false, width: '100px' }
]

const fetchLogs = async () => {
  loadingLogs.value = true
  try {
    const res = await fetch('/api/admin/logs', { headers: getAuthHeaders() })
    if (res.ok) {
      const json = await res.json()
      logs.value = json.data || []
    }
  } catch (err) {
    console.error('Error fetching logs:', err)
  } finally {
    loadingLogs.value = false
  }
}

watch(activeTab, (newTab) => {
  if (newTab === 'logs' && logs.value.length === 0) {
    fetchLogs()
  }
})

const filteredLogs = computed(() => {
  let list = logs.value
  
  if (logsFilterType.value !== 'todos') {
    list = list.filter(l => l.tipo === logsFilterType.value)
  }
  
  if (searchLog.value) {
    const s = searchLog.value.toLowerCase()
    list = list.filter(l => 
      (l.accion || '').toLowerCase().includes(s) ||
      (l.tipo || '').toLowerCase().includes(s) ||
      (l.ip || '').toLowerCase().includes(s) ||
      JSON.stringify(l.detalles || {}).toLowerCase().includes(s)
    )
  }
  
  return list
})

const openLogDetails = (item) => {
  selectedLog.value = item
  dialogLogDetails.value = true
}

const getLogTypeColor = (type) => {
  const map = {
    'info': 'success',
    'warning': 'warning',
    'error': 'error',
    'critical': 'error'
  }
  return map[type] || 'primary'
}

const getLogTypeIcon = (type) => {
  const map = {
    'info': 'mdi-information-outline',
    'warning': 'mdi-alert-outline',
    'error': 'mdi-alert-octagon-outline',
    'critical': 'mdi-fire'
  }
  return map[type] || 'mdi-text-box-outline'
}

const stats = ref({
  total_usuarios: 0,
  usuarios_activos: 0,
  usuarios_inactivos: 0,
  usuarios_por_rol: {},
  total_servicios: 0,
  servicios_activos: 0,
  total_reservas: 0,
  reservas_finalizadas: 0,
  reservas_canceladas: 0,
  reservas_por_estado: {},
  reservas_por_dia: [],
  ingresos_totales: 0,
  ingresos_por_metodo: {},
  promedio_calificacion: null,
  total_calificaciones: 0,
  calificaciones_por_puntuacion: {},
  top_servicios: [],
  ultimas_reservas: [],
  ultimos_usuarios: [],
})

// ── Canvas refs ───────────────────────────────────────────────────────────────
const barChartRef      = ref(null)
const doughnutChartRef = ref(null)
let barChartInstance      = null
let doughnutChartInstance = null

// ── Computed ──────────────────────────────────────────────────────────────────
const totalReservas       = computed(() => stats.value.total_reservas || 0)
const totalCalificaciones = computed(() => stats.value.total_calificaciones || 0)
const maxTopService       = computed(() =>
  Math.max(...(stats.value.top_servicios || []).map(s => s.total_reservas), 1)
)
const reservasPorDiaLabels = computed(() => (stats.value.reservas_por_dia || []).map(r => {
  const parts = r.dia.split('-')
  if (parts.length === 3) {
    const [year, month, day] = parts
    return `${day}/${month}`
  }
  return r.dia
}))
const reservasPorDiaData = computed(() => (stats.value.reservas_por_dia || []).map(r => r.total))

// ── Table headers ─────────────────────────────────────────────────────────────
const reservaHeaders = [
  { title: '#',        key: 'id',               width: '60px' },
  { title: 'Cliente',  key: 'cliente_nombre' },
  { title: 'Servicio', key: 'servicio_nombre' },
  { title: 'Estado',   key: 'estado',           width: '150px' },
  { title: 'Fecha',    key: 'fecha_hora_inicio', width: '180px' },
  { title: 'Creada',   key: 'created_at',       width: '170px' },
]
const userHeaders = [
  { title: '#',              key: 'id',              width: '60px' },
  { title: 'Nombre',         key: 'nombre' },
  { title: 'Email',          key: 'email' },
  { title: 'Rol',            key: 'role',            width: '130px' },
  { title: 'Estado',         key: 'activo',          width: '110px' },
  { title: 'Registro',       key: 'fecha_registro',  width: '170px' },
]

// ── Helpers ───────────────────────────────────────────────────────────────────
// getAuthHeaders imported from useAuth

const gradientStyle = (color) =>
  `background: linear-gradient(135deg, ${color}dd, ${color}99);`

const formatCurrency = (v) =>
  v != null
    ? new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN', maximumFractionDigits: 0 }).format(v)
    : 'S/. 0'

// formatDateTime imported from useDateFormatter
// getEstadoColor and getEstadoIcon imported from useReservationStatus

const getRoleColor     = (role) => ({ cliente: '#3B82F6', profesional: '#F97316', admin: '#8B5CF6' }[role] || '#6B7280')
const getRoleColorName = (role) => ({ cliente: 'blue', profesional: 'orange', admin: 'deep-purple' }[role] || 'grey')
const getRoleLabel     = (role) => ({ cliente: 'Cliente', profesional: 'Profesional', admin: 'Admin' }[role] || role)

// ── Charts ────────────────────────────────────────────────────────────────────
const renderBarChart = () => {
  if (!barChartRef.value || !Chart) return
  barChartInstance?.destroy()
  barChartInstance = new Chart(barChartRef.value, {
    type: 'bar',
    data: {
      labels: reservasPorDiaLabels.value,
      datasets: [{
        label: 'Reservas',
        data: reservasPorDiaData.value,
        backgroundColor: 'rgba(99, 102, 241, 0.7)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 1.5,
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
        x: { grid: { display: false } },
      },
    },
  })
}

const renderDoughnutChart = () => {
  if (!doughnutChartRef.value || !Chart) return
  const roles  = Object.keys(stats.value.usuarios_por_rol || {})
  const counts = Object.values(stats.value.usuarios_por_rol || {}).map(Number)
  doughnutChartInstance?.destroy()
  doughnutChartInstance = new Chart(doughnutChartRef.value, {
    type: 'doughnut',
    data: {
      labels: roles.map(getRoleLabel),
      datasets: [{
        data: counts,
        backgroundColor: roles.map(getRoleColor),
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
      }],
    },
    options: {
      responsive: true,
      cutout: '65%',
      plugins: { legend: { display: false } },
    },
  })
}

// ── Data fetching ─────────────────────────────────────────────────────────────
const fetchStats = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/admin/stats', { headers: getAuthHeaders() })
    if (res.ok) {
      stats.value = await res.json()
      lastUpdated.value = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
      await nextTick()
      setTimeout(() => {
        renderBarChart()
        renderDoughnutChart()
      }, 100)
    }
  } catch (err) {
    console.error('Error fetching stats:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchStats())
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #8C6D46 0%, #A6987A 100%);
}
.card-hover {
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: default;
}
.card-hover:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.12) !important;
}
.h-100 { height: 100%; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  text-align: center;
}

.role-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  flex-shrink: 0;
}

.rank-badge {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 800;
  flex-shrink: 0;
  background: #e2e8f0;
  color: #475569;
}
.rank-1 { background: #fbbf24; color: #78350f; }
.rank-2 { background: #d1d5db; color: #374151; }
.rank-3 { background: #d97706; color: #fff; }

.hover-item {
  transition: background 0.15s;
}
.hover-item:hover {
  background: rgba(0,0,0,0.025);
}
</style>
