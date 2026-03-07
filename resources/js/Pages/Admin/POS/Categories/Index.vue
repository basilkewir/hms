<template>
  <DashboardLayout title="Categories Management" :user="user" :navigation="navigation">
    <!-- Categories Management Header -->
    <div class="bg-kotel-dark/90 backdrop-blur-xl shadow-2xl rounded-xl p-6 mb-8 border border-kotel-yellow/30">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-kotel-yellow">Categories Management</h1>
          <p class="text-kotel-sky-blue mt-2">Manage your product categories</p>
        </div>
        <button 
          @click="showAddCategory = true"
          class="bg-kotel-yellow hover:bg-kotel-yellow/80 text-kotel-black px-4 py-2 rounded-md text-sm font-medium transition-colors"
        >
          Add New Category
        </button>
      </div>
    </div>

    <!-- Categories Grid -->
    <div class="bg-kotel-dark/90 backdrop-blur-xl shadow-xl rounded-xl border border-kotel-yellow/30">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="category in categories" 
            :key="category.id"
            class="bg-kotel-black/50 border border-kotel-yellow/30 rounded-lg p-6 hover:shadow-md hover:bg-kotel-yellow/10 transition-all"
          >
            <div class="flex items-center justify-between mb-4">
              <div 
                class="w-4 h-4 rounded-full"
                :style="`background-color: ${category.color}`"
              ></div>
              <div class="flex space-x-2">
                <button 
                  @click="editCategory(category)"
                  class="text-kotel-yellow hover:text-kotel-yellow/80 text-sm transition-colors"
                >
                  Edit
                </button>
                <button 
                  @click="deleteCategory(category.id)"
                  class="text-red-400 hover:text-red-300 text-sm transition-colors"
                >
                  Delete
                </button>
              </div>
            </div>
            
            <h3 class="text-lg font-semibold text-white mb-2">{{ category.name }}</h3>
            <p class="text-sm text-kotel-sky-blue/70 mb-4">{{ category.products_count }} products</p>
            
            <div class="flex items-center justify-between">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="category.is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
              >
                {{ category.is_active ? 'Active' : 'Inactive' }}
              </span>
              <span class="text-xs text-kotel-sky-blue/50">{{ category.color }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <DialogModal :show="showAddCategory" @close="showAddCategory = false">
      <template #title>
        <span class="text-kotel-yellow">{{ editingCategory ? 'Edit Category' : 'Add New Category' }}</span>
      </template>
      <template #content>
        <div class="space-y-4 bg-kotel-dark">
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Category Name</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="w-full bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
              placeholder="Enter category name"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-kotel-sky-blue mb-2">Color</label>
            <div class="flex items-center space-x-3">
              <input 
                v-model="form.color" 
                type="color" 
                class="h-10 w-20 border border-kotel-yellow/30 rounded-md bg-kotel-black/50"
              >
              <input 
                v-model="form.color" 
                type="text" 
                class="flex-1 bg-kotel-black/50 border-kotel-yellow/30 text-white rounded-md shadow-sm focus:border-kotel-yellow focus:ring-kotel-yellow"
                placeholder="#3b82f6"
              >
            </div>
          </div>
          <div>
            <label class="flex items-center">
              <input 
                v-model="form.is_active" 
                type="checkbox" 
                class="rounded border-kotel-yellow/30 text-kotel-yellow shadow-sm focus:border-kotel-yellow focus:ring focus:ring-kotel-yellow/20 focus:ring-opacity-50 bg-kotel-black/50"
              >
              <span class="ml-2 text-sm text-kotel-sky-blue">Active</span>
            </label>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showAddCategory = false">Cancel</SecondaryButton>
        <PrimaryButton @click="saveCategory" class="ml-3">
          {{ editingCategory ? 'Update' : 'Create' }} Category
        </PrimaryButton>
      </template>
    </DialogModal>
  </DashboardLayout>
</template>

<script>
import { ref, computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { router } from '@inertiajs/vue3'
import { getNavigationForRole } from '@/Utils/navigation.js'

export default {
  components: {
    DashboardLayout,
    DialogModal,
    PrimaryButton,
    SecondaryButton
  },
  props: {
    categories: Array,
    user: Object
  },
  setup(props) {
    const showAddCategory = ref(false)
    const editingCategory = ref(null)
    
    const navigation = computed(() => {
      const role = props.user.roles[0]?.name || 'staff'
      return getNavigationForRole(role)
    })
    
    const form = ref({
      name: '',
      color: '#3b82f6',
      is_active: true
    })

    const editCategory = (category) => {
      editingCategory.value = category
      form.value = {
        name: category.name,
        color: category.color,
        is_active: category.is_active
      }
      showAddCategory.value = true
    }

    const saveCategory = () => {
      const url = editingCategory.value 
        ? `/admin/pos/categories/${editingCategory.value.id}` 
        : '/admin/pos/categories'
      
      const method = editingCategory.value ? 'put' : 'post'
      
      router[method](url, form.value, {
        onSuccess: () => {
          showAddCategory.value = false
          editingCategory.value = null
          resetForm()
        }
      })
    }

    const deleteCategory = (id) => {
      if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/admin/pos/categories/${id}`)
      }
    }

    const resetForm = () => {
      form.value = {
        name: '',
        color: '#3b82f6',
        is_active: true
      }
    }

    return {
      showAddCategory,
      editingCategory,
      form,
      navigation,
      editCategory,
      saveCategory,
      deleteCategory,
      resetForm
    }
  }
}
</script>