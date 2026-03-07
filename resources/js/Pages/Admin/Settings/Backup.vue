<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const navigation = computed(() => page.props.navigation)
const flash = computed(() => page.props.flash ?? {})

const props = defineProps({
    backupSettings: { type: Object, default: null },
    backupStatus:   { type: Object, default: null },
    backupHistory:  { type: Array,  default: () => [] },
})

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    success:       'var(--kotel-success)',
    warning:       'var(--kotel-warning)',
    danger:        'var(--kotel-danger)',
}))

const creating = ref(false)

const settings = ref({
    frequency:        'daily',
    backup_time:      '02:00',
    retention_days:   30,
    storage_location: 'local',
    include_database: true,
    include_files:    true,
    include_uploads:  true,
    include_logs:     false,
})

const status = computed(() => props.backupStatus || {
    lastBackup:  'Never',
    nextBackup:  'Configured by schedule',
    storageUsed: '0 MB',
})

const history = computed(() => props.backupHistory || [])

const typeStyle = (type) => {
    const map = {
        automatic: { bg: themeColors.value.primary  + '20', color: themeColors.value.primary  },
        manual:    { bg: themeColors.value.success   + '20', color: themeColors.value.success   },
        scheduled: { bg: themeColors.value.warning   + '20', color: themeColors.value.warning   },
    }
    return map[type] || { bg: themeColors.value.textTertiary + '20', color: themeColors.value.textTertiary }
}

const statusStyle = (s) => {
    const map = {
        completed:   { bg: themeColors.value.success + '20', color: themeColors.value.success },
        in_progress: { bg: themeColors.value.warning + '20', color: themeColors.value.warning },
        failed:      { bg: themeColors.value.danger  + '20', color: themeColors.value.danger  },
    }
    return map[s] || { bg: themeColors.value.textTertiary + '20', color: themeColors.value.textTertiary }
}

const capitalize = (str) => str?.charAt(0).toUpperCase() + str?.slice(1)
const formatDT   = (d)   => d ? new Date(d).toLocaleString() : '—'

const createBackup = () => {
    creating.value = true
    router.post(route('admin.settings.backup.create'), {}, {
        onFinish: () => creating.value = false,
    })
}

const saveSettings = () => {
    const payload = {
        'backup.frequency':        { value: settings.value.frequency,        group: 'backup' },
        'backup.backup_time':      { value: settings.value.backup_time,      group: 'backup' },
        'backup.retention_days':   { value: settings.value.retention_days,   group: 'backup', type: 'integer' },
        'backup.storage_location': { value: settings.value.storage_location, group: 'backup' },
        'backup.include_database': { value: settings.value.include_database, group: 'backup', type: 'boolean' },
        'backup.include_files':    { value: settings.value.include_files,    group: 'backup', type: 'boolean' },
        'backup.include_uploads':  { value: settings.value.include_uploads,  group: 'backup', type: 'boolean' },
        'backup.include_logs':     { value: settings.value.include_logs,     group: 'backup', type: 'boolean' },
    }
    router.post(route('admin.settings.update'), { settings: payload })
}

const downloadBackup = (backup) => {
    window.location.href = route('admin.settings.backup.download', backup.name)
}

const restoreBackup = (backup) => {
    if (confirm(`Restore from "${backup.name}"? This will overwrite current data.`)) {
        router.post(route('admin.settings.backup.restore', backup.name))
    }
}

const deleteBackup = (backup) => {
    if (confirm(`Delete backup "${backup.name}"?`)) {
        router.delete(route('admin.settings.backup.delete', backup.name))
    }
}

onMounted(() => {
    if (props.backupSettings) Object.assign(settings.value, props.backupSettings)
})
</script>

<template>
    <DashboardLayout title="System Backup" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">System Backup</h1>
                <p :style="{ color: themeColors.textSecondary }">Configure automated backups and manage backup files.</p>
            </div>
            <button @click="createBackup" :disabled="creating"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                    :style="{ backgroundColor: themeColors.success }">
                {{ creating ? 'Creating…' : '+ Create Backup Now' }}
            </button>
        </div>

        <!-- Flash messages -->
        <div v-if="flash.success" class="mb-6 px-4 py-3 rounded-lg border text-sm"
             :style="{ backgroundColor: themeColors.success + '15', borderColor: themeColors.success + '40', color: themeColors.success }">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="mb-6 px-4 py-3 rounded-lg border text-sm"
             :style="{ backgroundColor: themeColors.danger + '15', borderColor: themeColors.danger + '40', color: themeColors.danger }">
            {{ flash.error }}
        </div>

        <!-- Status cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div v-for="(val, label) in { 'Last Backup': status.lastBackup, 'Next Backup': status.nextBackup, 'Storage Used': status.storageUsed }"
                 :key="label"
                 class="rounded-xl border p-5"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <p class="text-xs font-medium mb-1" :style="{ color: themeColors.textSecondary }">{{ label }}</p>
                <p class="text-lg font-bold" :style="{ color: themeColors.textPrimary }">{{ val }}</p>
            </div>
        </div>

        <!-- Configuration -->
        <div class="rounded-xl border p-6 mb-8" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <h2 class="text-lg font-semibold mb-6" :style="{ color: themeColors.textPrimary }">Backup Configuration</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Frequency</label>
                    <select v-model="settings.frequency" class="w-full px-3 py-2 rounded-lg border text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="manual">Manual Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Backup Time</label>
                    <input type="time" v-model="settings.backup_time"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Retention Period (days)</label>
                    <input type="number" v-model="settings.retention_days" min="1" max="365"
                           class="w-full px-3 py-2 rounded-lg border text-sm"
                           :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Storage Location</label>
                    <select v-model="settings.storage_location" class="w-full px-3 py-2 rounded-lg border text-sm"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }">
                        <option value="local">Local Storage</option>
                        <option value="s3">Amazon S3</option>
                        <option value="google">Google Cloud</option>
                        <option value="dropbox">Dropbox</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <p class="text-sm font-medium mb-3" :style="{ color: themeColors.textPrimary }">Include in backup</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label v-for="(model, label) in {
                        'Database':         'include_database',
                        'App Files':        'include_files',
                        'Uploaded Files':   'include_uploads',
                        'System Logs':      'include_logs',
                    }" :key="label" class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="settings[model]" class="rounded" />
                        <span class="text-sm" :style="{ color: themeColors.textSecondary }">{{ label }}</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="saveSettings"
                        class="px-5 py-2 rounded-lg text-sm font-medium text-white"
                        :style="{ backgroundColor: themeColors.primary }">
                    Save Settings
                </button>
            </div>
        </div>

        <!-- Backup history -->
        <div class="rounded-xl border overflow-hidden"
             :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h2 class="font-semibold" :style="{ color: themeColors.textPrimary }">Backup History</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr :style="{ backgroundColor: themeColors.background }">
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Name</th>
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Type</th>
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Size</th>
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Created</th>
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Status</th>
                        <th class="text-left p-4 text-xs font-medium uppercase" :style="{ color: themeColors.textSecondary }">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in history" :key="b.id"
                        class="border-t" :style="{ borderColor: themeColors.border }">
                        <td class="p-4 text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ b.name }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-xs font-medium"
                                  :style="{ backgroundColor: typeStyle(b.type).bg, color: typeStyle(b.type).color }">
                                {{ capitalize(b.type) }}
                            </span>
                        </td>
                        <td class="p-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ b.size }}</td>
                        <td class="p-4 text-sm" :style="{ color: themeColors.textSecondary }">{{ formatDT(b.created_at) }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-xs font-medium"
                                  :style="{ backgroundColor: statusStyle(b.status).bg, color: statusStyle(b.status).color }">
                                {{ b.status?.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3 text-sm">
                                <button @click="downloadBackup(b)" class="hover:underline" :style="{ color: themeColors.primary }">Download</button>
                                <button @click="restoreBackup(b)" class="hover:underline" :style="{ color: themeColors.success }">Restore</button>
                                <button @click="deleteBackup(b)" class="hover:underline" :style="{ color: themeColors.danger }">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="history.length === 0">
                        <td colspan="6" class="p-10 text-center text-sm" :style="{ color: themeColors.textTertiary }">
                            No backups found. Click "Create Backup Now" to create one.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardLayout>
</template>
