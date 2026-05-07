<template>
  <div class="image-upload">
    <el-upload
      ref="uploadRef"
      :action="uploadUrl"
      :headers="headers"
      :data="extraData"
      :name="fileName"
      :accept="accept"
      :limit="limit"
      :disabled="disabled"
      :drag="drag"
      :multiple="multiple"
      :auto-upload="autoUpload"
      :list-type="listType"
      :show-file-list="showFileList"
      :before-upload="handleBeforeUpload"
      :on-preview="handlePreview"
      :on-remove="handleRemove"
      :on-success="handleSuccess"
      :on-error="handleError"
      :on-progress="handleProgress"
      :on-change="handleChange"
      :http-request="httpRequest"
      class="upload-component"
    >
      <slot>
        <div v-if="drag" class="upload-drag-content">
          <el-icon class="upload-icon"><UploadFilled /></el-icon>
          <div class="upload-text">
            <span>将文件拖到此处，或</span>
            <em>点击上传</em>
          </div>
        </div>
        <div v-else class="upload-button">
          <el-icon v-if="loading" class="upload-loading"><Loading /></el-icon>
          <el-icon v-else><Plus /></el-icon>
          <div class="upload-text">{{ buttonText }}</div>
        </div>
      </slot>

      <template #tip v-if="showTip">
        <div class="upload-tip">
          <slot name="tip">
            <span>{{ tipText }}</span>
          </slot>
        </div>
      </template>
    </el-upload>

    <el-dialog v-model="previewVisible" title="图片预览" width="600px">
      <img :src="previewUrl" alt="Preview" style="width: 100%" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { UploadFilled, Plus, Loading } from '@element-plus/icons-vue'
import { useUserStore } from '@/store/modules/user'

const props = defineProps({
  modelValue: {
    type: [String, Array],
    default: ''
  },
  action: {
    type: String,
    default: '/api/upload/image'
  },
  fileName: {
    type: String,
    default: 'file'
  },
  headers: {
    type: Object,
    default: () => ({})
  },
  data: {
    type: Object,
    default: () => ({})
  },
  accept: {
    type: String,
    default: 'image/*'
  },
  limit: {
    type: Number,
    default: 5
  },
  disabled: Boolean,
  drag: Boolean,
  multiple: {
    type: Boolean,
    default: true
  },
  autoUpload: {
    type: Boolean,
    default: true
  },
  listType: {
    type: String,
    default: 'picture-card'
  },
  showFileList: {
    type: Boolean,
    default: true
  },
  showTip: {
    type: Boolean,
    default: true
  },
  tipText: {
    type: String,
    default: '支持 jpg、png、gif 格式，单个文件不超过 5MB'
  },
  buttonText: {
    type: String,
    default: '上传图片'
  },
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024
  },
  imageUrl: String
})

const emit = defineEmits([
  'update:modelValue',
  'change',
  'success',
  'error',
  'remove',
  'preview'
])

const uploadRef = ref(null)
const loading = ref(false)
const previewVisible = ref(false)
const previewUrl = ref('')

const userStore = useUserStore()

const uploadUrl = computed(() => {
  return props.action.startsWith('/') ? import.meta.env.VITE_API_BASE_URL + props.action : props.action
})

const headers = computed(() => {
  const defaultHeaders = {}
  if (userStore.token) {
    defaultHeaders['Authorization'] = `Bearer ${userStore.token}`
  }
  return { ...defaultHeaders, ...props.headers }
})

const extraData = computed(() => {
  return props.data
})

const fileList = computed({
  get: () => {
    if (!props.modelValue) return []
    if (Array.isArray(props.modelValue)) {
      return props.modelValue.map((url, index) => ({
        url,
        uid: index
      }))
    }
    return [{ url: props.modelValue, uid: Date.now() }]
  },
  set: (val) => {
    if (props.multiple) {
      emit('update:modelValue', val.map(v => v.url))
    } else {
      emit('update:modelValue', val.length > 0 ? val[0].url : '')
    }
  }
})

const handleBeforeUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLtSize = file.size <= props.maxSize

  if (!isImage) {
    ElMessage.error('只能上传图片文件')
    return false
  }
  if (!isLtSize) {
    ElMessage.error(`文件大小不能超过 ${props.maxSize / 1024 / 1024}MB`)
    return false
  }

  loading.value = true
  return true
}

const handleProgress = (event, file) => {
  loading.value = event.percent < 100
}

const handleSuccess = (response, file, fileList) => {
  loading.value = false
  
  if (response.code === 200) {
    const newFileList = fileList.map(f => ({
      ...f,
      url: f.response?.data?.url || response.data?.url || f.url
    }))
    fileList.value = newFileList
    emit('update:modelValue', props.multiple ? newFileList.map(f => f.url) : (newFileList[0]?.url || ''))
    emit('success', response)
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
    emit('error', response)
  }
}

const handleError = (error, file, fileList) => {
  loading.value = false
  ElMessage.error('上传失败，请重试')
  emit('error', error)
}

const handleRemove = (file, fileList) => {
  const newList = fileList.map(f => ({
    ...f,
    url: f.response?.data?.url || f.url
  }))
  fileList.value = newList
  emit('update:modelValue', props.multiple ? newList.map(f => f.url) : '')
  emit('remove', file)
}

const handlePreview = (file) => {
  const url = file.response?.data?.url || file.url
  previewUrl.value = url
  previewVisible.value = true
  emit('preview', file)
}

const handleChange = (file, fileList) => {
  emit('change', { file, fileList })
}

const httpRequest = (options) => {
  const { action, file, headers, data, filename, onProgress, onSuccess, onError } = options
  
  const formData = new FormData()
  formData.append(filename, file)
  
  if (data) {
    Object.keys(data).forEach(key => {
      formData.append(key, data[key])
    })
  }

  const xhr = new XMLHttpRequest()
  xhr.open('POST', action)
  
  Object.keys(headers).forEach(key => {
    xhr.setRequestHeader(key, headers[key])
  })

  xhr.upload.onprogress = (event) => {
    if (event.lengthComputable) {
      onProgress({ percent: Math.round((event.loaded / event.total) * 100) })
    }
  }

  xhr.onload = () => {
    if (xhr.status >= 200 && xhr.status < 300) {
      const response = JSON.parse(xhr.responseText)
      onSuccess(response)
    } else {
      onError(new Error(xhr.statusText))
    }
  }

  xhr.onerror = () => {
    onError(new Error('Network Error'))
  }

  xhr.send(formData)

  return {
    abort: () => {
      xhr.abort()
    }
  }
}

const clearFiles = () => {
  uploadRef.value?.clearFiles()
}

const submit = () => {
  uploadRef.value?.submit()
}

defineExpose({
  uploadRef,
  clearFiles,
  submit
})
</script>

<style scoped>
.upload-component {
  width: 100%;
}

.upload-component :deep(.el-upload) {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
}

.upload-component :deep(.el-upload:hover) {
  border-color: #409eff;
}

.upload-drag-content,
.upload-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  min-width: 148px;
  min-height: 148px;
}

.upload-icon,
.upload-loading {
  font-size: 48px;
  color: #909399;
  margin-bottom: 12px;
}

.upload-loading {
  animation: rotating 2s linear infinite;
}

@keyframes rotating {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.upload-text {
  color: #606266;
  font-size: 14px;
  text-align: center;
}

.upload-text em {
  color: #409eff;
  font-style: normal;
}

.upload-tip {
  margin-top: 8px;
  color: #909399;
  font-size: 12px;
}

:deep(.el-upload-list--picture-card) {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

:deep(.el-upload-list__item) {
  width: 148px;
  height: 148px;
  margin: 0;
}
</style>
