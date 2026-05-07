<template>
  <el-dialog
    v-model="visible"
    :title="dialogTitle"
    :width="width"
    :fullscreen="fullscreen"
    :top="top"
    :modal="modal"
    :close-on-click-modal="closeOnClickModal"
    :close-on-press-escape="closeOnPressEscape"
    :show-close="showClose"
    :destroy-on-close="destroyOnClose"
    @closed="handleClosed"
    class="form-dialog"
  >
    <el-form
      ref="formRef"
      :model="formData"
      :rules="formRules"
      :label-width="labelWidth"
      :label-position="labelPosition"
      :disabled="formDisabled"
      :validate-on-rule-change="validateOnRuleChange"
    >
      <el-row :gutter="gutter">
        <el-col
          v-for="field in visibleFields"
          :key="field.prop"
          :span="field.span || defaultSpan"
        >
          <el-form-item :label="field.label" :prop="field.prop" :label-width="field.labelWidth || labelWidth">
            <slot :name="'form-' + field.prop" :field="field" :formData="formData">
              <component
                :is="getComponentType(field)"
                v-model="formData[field.prop]"
                v-bind="getFieldProps(field)"
                :placeholder="field.placeholder || `请输入${field.label}`"
                :clearable="field.clearable !== false && !formDisabled"
                :disabled="field.disabled || formDisabled"
              >
                <template v-if="field.type === 'select' || field.type === 'radio-group' || field.type === 'checkbox-group'">
                  <component
                    :is="getOptionComponent(field)"
                    v-for="option in field.options"
                    :key="option.value"
                    :label="field.type === 'radio-group' || field.type === 'checkbox-group' ? option.label : undefined"
                    :value="option.value"
                    :disabled="option.disabled"
                  >
                    {{ field.type === 'select' ? option.label : undefined }}
                  </component>
                </template>
              </component>
            </slot>
          </el-form-item>
        </el-col>
      </el-row>

      <slot name="form-extra" :formData="formData" />
    </el-form>

    <template #footer v-if="showFooter !== false">
      <slot name="dialog-footer" :formData="formData" :handleClose="handleClose" :handleSubmit="handleSubmit">
        <el-button @click="handleClose">
          {{ cancelText || '取消' }}
        </el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          {{ confirmText || '确定' }}
        </el-button>
      </slot>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: String,
  width: {
    type: String,
    default: '600px'
  },
  fullscreen: Boolean,
  top: {
    type: String,
    default: '15vh'
  },
  modal: {
    type: Boolean,
    default: true
  },
  closeOnClickModal: {
    type: Boolean,
    default: false
  },
  closeOnPressEscape: {
    type: Boolean,
    default: true
  },
  showClose: {
    type: Boolean,
    default: true
  },
  destroyOnClose: {
    type: Boolean,
    default: true
  },
  fields: {
    type: Array,
    default: () => []
  },
  model: {
    type: Object,
    default: () => ({})
  },
  rules: {
    type: Object,
    default: () => ({})
  },
  labelWidth: {
    type: String,
    default: '120px'
  },
  labelPosition: {
    type: String,
    default: 'right'
  },
  gutter: {
    type: Number,
    default: 20
  },
  defaultSpan: {
    type: Number,
    default: 24
  },
  disabled: Boolean,
  showFooter: {
    type: Boolean,
    default: true
  },
  confirmText: String,
  cancelText: String,
  submitLoading: Boolean,
  validateOnRuleChange: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue',
  'open',
  'close',
  'closed',
  'submit',
  'validate'
])

const formRef = ref(null)
const formData = ref({})
const formRules = ref({})
const formDisabled = ref(false)
const dialogTitle = computed(() => props.title || '表单')

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const visibleFields = computed(() => {
  return props.fields.filter(field => field.show !== false)
})

watch(() => props.model, (newVal) => {
  if (newVal && Object.keys(newVal).length > 0) {
    formData.value = { ...newVal }
  }
}, { immediate: true, deep: true })

watch(() => props.fields, (newFields) => {
  const rules = {}
  const data = {}
  newFields.forEach(field => {
    if (field.prop) {
      if (field.rules) {
        rules[field.prop] = field.rules
      }
      if (field.defaultValue !== undefined) {
        data[field.prop] = field.defaultValue
      }
    }
  })
  formRules.value = { ...props.rules, ...rules }
  
  Object.keys(data).forEach(key => {
    if (formData.value[key] === undefined) {
      formData.value[key] = data[key]
    }
  })
}, { immediate: true, deep: true })

watch(() => props.disabled, (val) => {
  formDisabled.value = val
}, { immediate: true })

const getComponentType = (field) => {
  const typeMap = {
    input: 'el-input',
    textarea: 'el-input',
    number: 'el-input-number',
    select: 'el-select',
    date: 'el-date-picker',
    daterange: 'el-date-picker',
    datetime: 'el-date-picker',
    datetimerange: 'el-date-picker',
    time: 'el-time-picker',
    switch: 'el-switch',
    radio: 'el-radio-group',
    'radio-group': 'el-radio-group',
    checkbox: 'el-checkbox-group',
    'checkbox-group': 'el-checkbox-group',
    cascader: 'el-cascader',
    slider: 'el-slider',
    rate: 'el-rate',
    color: 'el-color-picker',
    upload: 'el-upload'
  }
  return typeMap[field.type] || 'el-input'
}

const getOptionComponent = (field) => {
  if (field.type === 'select') return 'el-option'
  if (field.type === 'radio-group') return 'el-radio'
  if (field.type === 'checkbox-group') return 'el-checkbox'
  return 'el-option'
}

const getFieldProps = (field) => {
  const result = {}

  if (field.type === 'input') {
    result.type = field.inputType || 'text'
    if (field.showPassword) result.showPassword = true
    if (field.prefixIcon) result.prefixIcon = field.prefixIcon
    if (field.suffixIcon) result.suffixIcon = field.suffixIcon
  }

  if (field.type === 'textarea') {
    result.type = 'textarea'
    result.rows = field.rows || 3
    result.maxlength = field.maxlength
    result.showWordLimit = field.showWordLimit || false
  }

  if (field.type === 'number') {
    result.min = field.min
    result.max = field.max
    result.step = field.step || 1
    result.precision = field.precision
    result.controls = field.controls !== false
  }

  if (field.type === 'select') {
    result.placeholder = field.placeholder || `请选择${field.label}`
    if (field.filterable) result.filterable = true
    if (field.multiple) result.multiple = true
    if (field.allowCreate) result.allowCreate = true
    if (field.defaultFirstOption) result.defaultFirstOption = true
  }

  if (field.type === 'date') {
    result.type = 'date'
    result.valueFormat = field.valueFormat || 'YYYY-MM-DD'
  }

  if (field.type === 'datetime') {
    result.type = 'datetime'
    result.valueFormat = field.valueFormat || 'YYYY-MM-DD HH:mm:ss'
  }

  if (field.type === 'daterange') {
    result.type = 'daterange'
    result.rangeSeparator = field.rangeSeparator || '至'
    result.startPlaceholder = field.startPlaceholder || '开始日期'
    result.endPlaceholder = field.endPlaceholder || '结束日期'
    result.valueFormat = field.valueFormat || 'YYYY-MM-DD'
  }

  if (field.type === 'datetimerange') {
    result.type = 'datetimerange'
    result.rangeSeparator = field.rangeSeparator || '至'
    result.startPlaceholder = field.startPlaceholder || '开始时间'
    result.endPlaceholder = field.endPlaceholder || '结束时间'
    result.valueFormat = field.valueFormat || 'YYYY-MM-DD HH:mm:ss'
  }

  if (field.type === 'cascader') {
    result.options = field.options || []
    result.props = field.props || { expandTrigger: 'hover' }
    result.placeholder = field.placeholder || `请选择${field.label}`
    if (field.showAllLevels !== undefined) result.showAllLevels = field.showAllLevels
  }

  if (field.type === 'upload') {
    result.action = field.action || '#'
    result.headers = field.headers || {}
    result.data = field.data || {}
    result.withCredentials = field.withCredentials || false
    result.accept = field.accept
    result.limit = field.limit
    if (field.listType) result.listType = field.listType
    if (field.autoUpload !== undefined) result.autoUpload = field.autoUpload
  }

  return result
}

const handleClose = () => {
  visible.value = false
  emit('close')
}

const handleSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
    emit('submit', { ...formData.value })
  } catch (error) {
    ElMessage.error('请检查表单填写是否正确')
    return Promise.reject(error)
  }
}

const handleClosed = () => {
  nextTick(() => {
    if (formRef.value) {
      formRef.value.resetFields()
    }
    emit('closed')
  })
}

const validate = () => {
  return formRef.value?.validate()
}

const resetFields = () => {
  formRef.value?.resetFields()
}

const clearValidate = () => {
  formRef.value?.clearValidate()
}

const validateField = (field) => {
  formRef.value?.validateField(field)
}

defineExpose({
  formRef,
  formData,
  validate,
  resetFields,
  clearValidate,
  validateField
})
</script>

<style scoped>
.form-dialog :deep(.el-dialog__header) {
  padding: 16px 20px;
  border-bottom: 1px solid #ebeef5;
}

.form-dialog :deep(.el-dialog__title) {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.form-dialog :deep(.el-dialog__body) {
  padding: 24px 20px;
}

.form-dialog :deep(.el-dialog__footer) {
  padding: 16px 20px;
  border-top: 1px solid #ebeef5;
}

.form-dialog :deep(.el-form-item) {
  margin-bottom: 22px;
}

.form-dialog :deep(.el-input-number) {
  width: 100%;
}
</style>
