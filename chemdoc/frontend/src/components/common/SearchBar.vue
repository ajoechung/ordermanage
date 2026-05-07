<template>
  <div class="search-bar">
    <el-form
      ref="formRef"
      :model="formData"
      :inline="inline"
      :label-width="labelWidth"
      :label-position="labelPosition"
      class="search-form"
    >
      <slot :formData="formData" :handleQuery="handleQuery" :handleReset="handleReset">
        <el-form-item
          v-for="field in visibleFields"
          :key="field.prop"
          :label="field.label"
          :prop="field.prop"
          :rules="field.rules"
        >
          <component
            :is="getComponentType(field)"
            v-model="formData[field.prop]"
            v-bind="getFieldProps(field)"
            :placeholder="field.placeholder || `请输入${field.label}`"
            :clearable="field.clearable !== false"
            :disabled="field.disabled"
            @change="(val) => handleFieldChange(field, val)"
          >
            <template v-if="field.type === 'select' || field.type === 'radio-group'">
              <el-option
                v-for="option in field.options"
                :key="option.value"
                :label="option.label"
                :value="option.value"
                :disabled="option.disabled"
              />
            </template>
          </component>
        </el-form-item>
      </slot>

      <el-form-item class="search-actions">
        <el-button type="primary" :icon="Search" @click="handleQuery" :loading="queryLoading">
          {{ queryText || '搜索' }}
        </el-button>
        <el-button :icon="Refresh" @click="handleReset">
          {{ resetText || '重置' }}
        </el-button>
        <slot name="extra-actions" />
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'

const props = defineProps({
  fields: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: Object,
    default: () => ({})
  },
  inline: {
    type: Boolean,
    default: true
  },
  labelWidth: {
    type: String,
    default: '100px'
  },
  labelPosition: {
    type: String,
    default: 'right'
  },
  queryText: String,
  resetText: String,
  queryLoading: Boolean
})

const emit = defineEmits(['update:modelValue', 'query', 'reset', 'field-change'])

const formRef = ref(null)

const formData = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const initialData = computed(() => {
  const data = {}
  props.fields.forEach(field => {
    data[field.prop] = field.defaultValue !== undefined ? field.defaultValue : ''
  })
  return data
})

const visibleFields = computed(() => {
  return props.fields.filter(field => field.show !== false)
})

const getComponentType = (field) => {
  const typeMap = {
    input: 'el-input',
    textarea: 'el-input',
    number: 'el-input-number',
    select: 'el-select',
    date: 'el-date-picker',
    daterange: 'el-date-picker',
    datetimerange: 'el-date-picker',
    time: 'el-time-picker',
    switch: 'el-switch',
    radio: 'el-radio-group',
    'radio-group': 'el-radio-group',
    checkbox: 'el-checkbox-group',
    'checkbox-group': 'el-checkbox-group',
    cascader: 'el-cascader'
  }
  return typeMap[field.type] || 'el-input'
}

const getFieldProps = (field) => {
  const props = {}

  if (field.type === 'input') {
    props.type = field.inputType || 'text'
    if (field.showPassword) props.showPassword = true
  }

  if (field.type === 'textarea') {
    props.type = 'textarea'
    props.rows = field.rows || 3
  }

  if (field.type === 'number') {
    props.min = field.min
    props.max = field.max
    props.step = field.step || 1
    props.precision = field.precision || 2
    props.controls = field.controls !== false
  }

  if (field.type === 'select') {
    props.placeholder = field.placeholder || `请选择${field.label}`
  }

  if (field.type === 'date' || field.type === 'time') {
    props.type = field.pickerType || field.type
    props.valueFormat = field.valueFormat || 'YYYY-MM-DD'
    if (field.type === 'time') props.format = field.format || 'HH:mm:ss'
  }

  if (field.type === 'daterange' || field.type === 'datetimerange') {
    props.type = field.type
    props.rangeSeparator = field.rangeSeparator || '至'
    props.startPlaceholder = field.startPlaceholder || '开始日期'
    props.endPlaceholder = field.endPlaceholder || '结束日期'
    props.valueFormat = field.valueFormat || 'YYYY-MM-DD'
  }

  if (field.type === 'cascader') {
    props.options = field.options || []
    props.props = field.props || { expandTrigger: 'hover' }
    props.placeholder = field.placeholder || `请选择${field.label}`
  }

  if (field.type === 'switch') {
    props.activeText = field.activeText
    props.inactiveText = field.inactiveText
    props.activeValue = field.activeValue !== undefined ? field.activeValue : true
    props.inactiveValue = field.inactiveValue !== undefined ? field.inactiveValue : false
  }

  return props
}

const handleFieldChange = (field, value) => {
  emit('field-change', { field, value })
}

const handleQuery = () => {
  emit('query', { ...formData.value })
}

const handleReset = () => {
  if (formRef.value) {
    formRef.value.resetFields()
  }
  emit('reset')
}

defineExpose({
  formRef,
  formData,
  handleQuery,
  handleReset
})
</script>

<style scoped>
.search-bar {
  background: #ffffff;
  border-radius: 4px;
}

.search-form {
  padding: 18px 18px 0;
}

.search-actions {
  margin-bottom: 18px;
}

:deep(.el-form-item) {
  margin-bottom: 18px;
}

:deep(.el-input-number) {
  width: 100%;
}
</style>
