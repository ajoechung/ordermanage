<template>
  <div class="crud-table">
    <el-table
      v-loading="loading"
      :data="tableData"
      stripe
      border
      :row-key="rowKey"
      :default-sort="{ prop: defaultSort?.prop, order: defaultSort?.order }"
      @selection-change="handleSelectionChange"
      @sort-change="handleSortChange"
      class="custom-table"
    >
      <el-table-column v-if="showSelection" type="selection" width="50" :reserve-selection="true" />

      <el-table-column
        v-for="column in visibleColumns"
        :key="column.prop"
        :prop="column.prop"
        :label="column.label"
        :width="column.width"
        :min-width="column.minWidth"
        :align="column.align || 'left'"
        :fixed="column.fixed"
        :sortable="column.sortable ? 'custom' : false"
        :show-overflow-tooltip="column.showOverflowTooltip !== false"
      >
        <template #default="{ row }">
          <slot :name="column.prop" :row="row" :column="column">
            {{ formatCell(row, column) }}
          </slot>
        </template>
      </el-table-column>

      <el-table-column
        v-if="showActions"
        :label="actionsLabel || '操作'"
        :width="actionsWidth || '200'"
        :fixed="actionsFixed || 'right'"
        align="center"
      >
        <template #default="{ row }">
          <slot name="actions" :row="row">
            <el-button
              v-if="showViewButton !== false"
              type="primary"
              link
              size="small"
              @click="handleView(row)"
            >
              查看
            </el-button>
            <el-button
              v-if="showEditButton !== false"
              type="primary"
              link
              size="small"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              v-if="showDeleteButton !== false"
              type="danger"
              link
              size="small"
              @click="handleDelete(row)"
            >
              删除
            </el-button>
          </slot>
        </template>
      </el-table-column>

      <template #empty>
        <el-empty :description="emptyText || '暂无数据'" />
      </template>
    </el-table>

    <div v-if="showPagination" class="pagination-wrapper">
      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :page-sizes="pageSizes"
        :total="total"
        :layout="paginationLayout"
        :background="paginationBackground"
        :small="paginationSmall"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
      />
      <div v-if="showTotal" class="pagination-total">
        共 {{ total }} 条
      </div>
    </div>

    <el-dialog
      v-model="detailVisible"
      :title="detailTitle"
      width="700px"
      :close-on-click-modal="false"
    >
      <el-descriptions :column="detailColumns || 2" border>
        <el-descriptions-item
          v-for="item in detailFields"
          :key="item.prop"
          :label="item.label"
          :span="item.span || 1"
        >
          <slot :name="'detail-' + item.prop" :value="currentRow?.[item.prop]" :row="currentRow">
            {{ item.formatter ? item.formatter(currentRow?.[item.prop], currentRow) : currentRow?.[item.prop] }}
          </slot>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer v-if="showDetailFooter">
        <slot name="detail-footer" :row="currentRow">
          <el-button @click="detailVisible = false">关闭</el-button>
        </slot>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  rowKey: {
    type: [String, Function],
    default: 'id'
  },
  showSelection: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  actionsLabel: String,
  actionsWidth: [String, Number],
  actionsFixed: {
    type: String,
    default: 'right'
  },
  showViewButton: {
    type: Boolean,
    default: true
  },
  showEditButton: {
    type: Boolean,
    default: true
  },
  showDeleteButton: {
    type: Boolean,
    default: true
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  total: {
    type: Number,
    default: 0
  },
  page: {
    type: Number,
    default: 1
  },
  pageSize: {
    type: Number,
    default: 10
  },
  pageSizes: {
    type: Array,
    default: () => [10, 20, 50, 100]
  },
  paginationLayout: {
    type: String,
    default: 'total, sizes, prev, pager, next, jumper'
  },
  paginationBackground: {
    type: Boolean,
    default: true
  },
  paginationSmall: {
    type: Boolean,
    default: false
  },
  showTotal: {
    type: Boolean,
    default: false
  },
  emptyText: String,
  defaultSort: Object,
  deleteConfirmText: {
    type: String,
    default: '确定要删除吗？'
  },
  detailTitle: {
    type: String,
    default: '详情'
  },
  detailColumns: {
    type: Number,
    default: 2
  },
  detailFields: {
    type: Array,
    default: () => []
  },
  showDetailFooter: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits([
  'update:page',
  'update:pageSize',
  'selection-change',
  'sort-change',
  'view',
  'edit',
  'delete',
  'query'
])

const currentPage = computed({
  get: () => props.page,
  set: (val) => emit('update:page', val)
})

const pageSize = computed({
  get: () => props.pageSize,
  set: (val) => emit('update:pageSize', val)
})

const tableData = computed(() => props.data)

const visibleColumns = computed(() => {
  return props.columns.filter(col => col.show !== false)
})

const detailVisible = ref(false)
const currentRow = ref(null)

const formatCell = (row, column) => {
  const value = row[column.prop]
  if (column.formatter) {
    return column.formatter(value, row)
  }
  if (value === null || value === undefined) {
    return column.nullText || '-'
  }
  return value
}

const handleSelectionChange = (rows) => {
  emit('selection-change', rows)
}

const handleSortChange = ({ prop, order }) => {
  emit('sort-change', { prop, order })
}

const handleSizeChange = (size) => {
  currentPage.value = 1
  emit('query')
}

const handlePageChange = (page) => {
  emit('query')
}

const handleView = (row) => {
  currentRow.value = row
  detailVisible.value = true
  emit('view', row)
}

const handleEdit = (row) => {
  emit('edit', row)
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(props.deleteConfirmText, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    emit('delete', row)
  } catch (error) {
    // Cancelled
  }
}

defineExpose({
  detailVisible,
  currentRow,
  handleView,
  handleEdit,
  handleDelete
})
</script>

<style scoped>
.crud-table {
  width: 100%;
}

.custom-table {
  font-size: 14px;
}

.custom-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: 600;
}

.custom-table :deep(.el-table__row:hover > td) {
  background-color: #f5f7fa;
}

.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 20px;
  gap: 16px;
}

.pagination-total {
  color: #606266;
  font-size: 14px;
}
</style>
