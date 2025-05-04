<!-- src/views/purchasing/PurchaseOrderDetail.vue -->
<template>
    <div class="po-detail-container">
      <div class="page-header">
        <div class="header-left">
          <router-link to="/purchasing/orders" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Purchase Orders
          </router-link>
          <h1>
            {{ purchaseOrder?.po_number || "Purchase Order Details" }}
          </h1>
        </div>
        <div class="header-actions">
          <div v-if="!isLoading && purchaseOrder" class="status-section">
            <span
              :class="[
                'status-badge',
                getStatusClass(purchaseOrder.status),
              ]"
            >
              {{ formatStatus(purchaseOrder.status) }}
            </span>
  
            <!-- Status update dropdown for authorized statuses -->
            <div v-if="canUpdateStatus" class="status-actions">
              <select v-model="selectedStatus" class="status-select">
                <option value="" disabled>Change Status</option>
                <option
                  v-for="status in availableStatusTransitions"
                  :key="status"
                  :value="status"
                >
                  {{ formatStatus(status) }}
                </option>
              </select>
              <button
                @click="updateStatus"
                :disabled="!selectedStatus || isUpdatingStatus"
                class="btn btn-sm btn-primary"
              >
                {{ isUpdatingStatus ? "Updating..." : "Update" }}
              </button>
            </div>
          </div>
  
          <div class="action-buttons">
            <router-link
              v-if="purchaseOrder && purchaseOrder.status === 'draft'"
              :to="`/purchasing/orders/${id}/edit`"
              class="btn btn-primary"
            >
              <i class="fas fa-edit"></i> Edit
            </router-link>
  
            <button
              v-if="purchaseOrder && purchaseOrder.status === 'draft'"
              @click="confirmDelete"
              class="btn btn-danger"
            >
              <i class="fas fa-trash"></i> Delete
            </button>
  
            <router-link
              :to="`/purchasing/orders/${id}/track`"
              class="btn btn-secondary"
            >
              <i class="fas fa-chart-line"></i> Track
            </router-link>
  
            <button class="btn btn-secondary" @click="printPO">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>
      </div>
  
      <div v-if="isLoading" class="loading-container">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading purchase order data...</p>
      </div>
  
      <div v-else-if="!purchaseOrder" class="error-container">
        <div class="error-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Purchase Order Not Found</h2>
        <p>
          The requested purchase order could not be found or may have been
          deleted.
        </p>
        <router-link to="/purchasing/orders" class="btn btn-primary">
          Return to Purchase Orders List
        </router-link>
      </div>
  
      <div v-else class="po-detail-content">
        <!-- Basic Information Card -->
        <div class="detail-card">
          <div class="card-header">
            <h2 class="card-title">Purchase Order Information</h2>
          </div>
          <div class="card-body">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">PO Number</span>
                <span class="info-value">{{
                  purchaseOrder.po_number
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">PO Date</span>
                <span class="info-value">{{
                  formatDate(purchaseOrder.po_date)
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Vendor</span>
                <span class="info-value">
                  <router-link
                    :to="`/purchasing/vendors/${purchaseOrder.vendor.vendor_id}`"
                  >
                    {{ purchaseOrder.vendor.name }}
                  </router-link>
                </span>
              </div>
              <div class="info-item">
                <span class="info-label">Expected Delivery</span>
                <span class="info-value">{{
                  formatDate(purchaseOrder.expected_delivery) ||
                  "Not specified"
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Payment Terms</span>
                <span class="info-value">{{
                  purchaseOrder.payment_terms || "Not specified"
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Delivery Terms</span>
                <span class="info-value">{{
                  purchaseOrder.delivery_terms || "Not specified"
                }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Currency</span>
                <span class="info-value">
                  {{ purchaseOrder.currency_code || "USD" }}
                </span>
              </div>
              <div class="info-item" v-if="purchaseOrder.exchange_rate && purchaseOrder.currency_code !== purchaseOrder.base_currency">
                <span class="info-label">Exchange Rate</span>
                <span class="info-value">
                  1 {{ purchaseOrder.currency_code }} = {{ purchaseOrder.exchange_rate }} {{ purchaseOrder.base_currency || "USD" }}
                </span>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Order Lines Card -->
        <div class="detail-card">
          <div class="card-header">
            <h2 class="card-title">Order Items</h2>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Unit Price</th>
                    <th>Tax</th>
                    <th>Subtotal</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(line, index) in purchaseOrder.lines"
                    :key="line.line_id"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>
                      <div class="item-name">
                        {{ line.item.name }}
                      </div>
                      <div class="item-code">
                        {{ line.item.item_code }}
                      </div>
                    </td>
                    <td>{{ line.quantity }}</td>
                    <td>{{ line.unitOfMeasure.name }}</td>
                    <td>
                      {{ formatCurrency(line.unit_price) }}
                    </td>
                    <td>{{ formatCurrency(line.tax) }}</td>
                    <td>{{ formatCurrency(line.subtotal) }}</td>
                    <td>{{ formatCurrency(line.total) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="6" class="text-right">
                      <strong>Subtotal:</strong>
                    </td>
                    <td colspan="2">
                      {{
                        formatCurrency(calculateSubtotal())
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="text-right">
                      <strong>Tax Total:</strong>
                    </td>
                    <td colspan="2">
                      {{
                        formatCurrency(
                          purchaseOrder.tax_amount
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="text-right">
                      <strong>Grand Total:</strong>
                    </td>
                    <td colspan="2" class="grand-total">
                      {{
                        formatCurrency(
                          purchaseOrder.total_amount
                        )
                      }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
  
        <!-- Related Records Section (Tabs) -->
        <div class="tabs-container">
          <div class="tabs-header">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              :class="[
                'tab-button',
                { active: activeTab === tab.id },
              ]"
              @click="activeTab = tab.id"
            >
              {{ tab.name }}
            </button>
          </div>
  
          <div class="tab-content">
            <!-- Goods Receipts Tab -->
            <div v-if="activeTab === 'receipts'" class="tab-pane">
              <div
                v-if="
                  purchaseOrder.goodsReceipts &&
                  purchaseOrder.goodsReceipts.length > 0
                "
              >
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Receipt Number</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th>Received By</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="receipt in purchaseOrder.goodsReceipts"
                      :key="receipt.receipt_id"
                    >
                      <td>{{ receipt.receipt_number }}</td>
                      <td>
                        {{
                          formatDate(receipt.receipt_date)
                        }}
                      </td>
                      <td>
                        <span
                          :class="[
                            'status-badge',
                            getReceiptStatusClass(
                              receipt.status
                            ),
                          ]"
                        >
                          {{ receipt.status }}
                        </span>
                      </td>
                      <td>
                        {{ receipt.received_by || "N/A" }}
                      </td>
                      <td>
                        <router-link
                          :to="`/purchasing/goods-receipts/${receipt.receipt_id}`"
                          class="action-btn view-btn"
                        >
                          <i class="fas fa-eye"></i>
                        </router-link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="empty-state">
                <div class="empty-icon">
                  <i class="fas fa-truck-loading"></i>
                </div>
                <h3>No Goods Receipts</h3>
                <p>
                  There are no goods receipts recorded for this
                  purchase order yet.
                </p>
                <div v-if="canCreateGoodsReceipt" class="empty-actions">
                  <router-link :to="`/purchasing/goods-receipts/create?po_id=${purchaseOrder.po_id}`" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Goods Receipt
                  </router-link>
                </div>
              </div>
            </div>
  
            <!-- Invoices Tab -->
            <div v-if="activeTab === 'invoices'" class="tab-pane">
              <div
                v-if="
                  purchaseOrder.invoices &&
                  purchaseOrder.invoices.length > 0
                "
              >
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Invoice Number</th>
                      <th>Date</th>
                      <th>Due Date</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="invoice in purchaseOrder.invoices"
                      :key="invoice.invoice_id"
                    >
                      <td>{{ invoice.invoice_number }}</td>
                      <td>
                        {{
                          formatDate(invoice.invoice_date)
                        }}
                      </td>
                      <td>
                        {{ formatDate(invoice.due_date) }}
                      </td>
                      <td>
                        {{
                          formatCurrency(
                            invoice.total_amount
                          )
                        }}
                      </td>
                      <td>
                        <span
                          :class="[
                            'status-badge',
                            getInvoiceStatusClass(
                              invoice.status
                            ),
                          ]"
                        >
                          {{ invoice.status }}
                        </span>
                      </td>
                      <td>
                        <router-link
                          :to="`/purchasing/vendor-invoices/${invoice.invoice_id}`"
                          class="action-btn view-btn"
                        >
                          <i class="fas fa-eye"></i>
                        </router-link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="empty-state">
                <div class="empty-icon">
                  <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3>No Invoices</h3>
                <p>
                  There are no vendor invoices recorded for this
                  purchase order yet.
                </p>
                <div v-if="canCreateInvoice" class="empty-actions">
                  <router-link :to="`/purchasing/vendor-invoices/create?po_id=${purchaseOrder.po_id}`" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Vendor Invoice
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Confirmation Modal for Delete -->
      <div v-if="showDeleteModal" class="modal-backdrop">
        <div class="modal-container">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Delete Purchase Order</h3>
              <button @click="closeDeleteModal" class="modal-close-btn">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body">
              <p>
                Are you sure you want to delete purchase order 
                <strong>{{ purchaseOrder?.po_number }}</strong>?
                This action cannot be undone.
              </p>
            </div>
            <div class="modal-footer">
              <button @click="closeDeleteModal" class="btn btn-secondary">
                Cancel
              </button>
              <button @click="deletePurchaseOrder" class="btn btn-danger">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import PurchaseOrderService from '@/services/PurchaseOrderService';
  
  export default {
    name: 'PurchaseOrderDetail',
    props: {
      id: {
        type: [Number, String],
        required: true
      }
    },
    data() {
      return {
        purchaseOrder: null,
        isLoading: true,
        showDeleteModal: false,
        activeTab: 'receipts',
        selectedStatus: '',
        isUpdatingStatus: false,
        
        // Available tabs
        tabs: [
          { id: 'receipts', name: 'Goods Receipts' },
          { id: 'invoices', name: 'Invoices' }
        ],
        
        // Status transitions map - defines which statuses can transition to which other statuses
        statusTransitions: {
          'draft': ['submitted', 'canceled'],
          'submitted': ['approved', 'canceled'],
          'approved': ['sent', 'canceled'],
          'sent': ['partial', 'received', 'canceled'],
          'partial': ['completed', 'canceled'],
          'received': ['completed', 'canceled'],
          'completed': ['canceled'],
          'canceled': []
        }
      };
    },
    computed: {
      // Available status transitions for current PO status
      availableStatusTransitions() {
        if (!this.purchaseOrder) return [];
        return this.statusTransitions[this.purchaseOrder.status] || [];
      },
      
      // Determine if user can update status
      canUpdateStatus() {
        return this.purchaseOrder && this.availableStatusTransitions.length > 0;
      },
      
      // Determine if user can create goods receipt
      canCreateGoodsReceipt() {
        const validStatuses = ['approved', 'sent', 'partial'];
        return this.purchaseOrder && validStatuses.includes(this.purchaseOrder.status);
      },
      
      // Determine if user can create invoice
      canCreateInvoice() {
        const validStatuses = ['approved', 'sent', 'partial', 'received', 'completed'];
        return this.purchaseOrder && validStatuses.includes(this.purchaseOrder.status);
      }
    },
    methods: {
      async fetchPurchaseOrder() {
        this.isLoading = true;
        
        try {
          const response = await PurchaseOrderService.getPurchaseOrderById(this.id);
          this.purchaseOrder = response.data.data || null;
        } catch (error) {
          console.error('Error fetching purchase order:', error);
          this.purchaseOrder = null;
        } finally {
          this.isLoading = false;
        }
      },
      
      formatDate(dateString) {
        if (!dateString) return null;
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: '2-digit'
        });
      },
      
      formatCurrency(amount) {
        if (amount === null || amount === undefined) return '$0.00';
        
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: this.purchaseOrder?.currency_code || 'USD'
        }).format(amount);
      },
      
      formatStatus(status) {
        switch (status) {
          case 'draft':
            return 'Draft';
          case 'submitted':
            return 'Submitted';
          case 'approved':
            return 'Approved';
          case 'sent':
            return 'Sent';
          case 'partial':
            return 'Partially Received';
          case 'received':
            return 'Received';
          case 'completed':
            return 'Completed';
          case 'canceled':
            return 'Canceled';
          default:
            return status;
        }
      },
      
      getStatusClass(status) {
        switch (status) {
          case 'draft':
            return 'status-draft';
          case 'submitted':
            return 'status-pending';
          case 'approved':
            return 'status-approved';
          case 'sent':
            return 'status-sent';
          case 'partial':
            return 'status-partial';
          case 'received':
            return 'status-received';
          case 'completed':
            return 'status-completed';
          case 'canceled':
            return 'status-canceled';
          default:
            return 'status-draft';
        }
      },
      
      getReceiptStatusClass(status) {
        switch (status) {
          case 'pending':
            return 'status-pending';
          case 'confirmed':
            return 'status-completed';
          case 'canceled':
            return 'status-canceled';
          default:
            return 'status-draft';
        }
      },
      
      getInvoiceStatusClass(status) {
        switch (status) {
          case 'pending':
            return 'status-pending';
          case 'approved':
            return 'status-approved';
          case 'paid':
            return 'status-completed';
          case 'canceled':
            return 'status-canceled';
          default:
            return 'status-draft';
        }
      },
      
      calculateSubtotal() {
        if (!this.purchaseOrder || !this.purchaseOrder.lines) return 0;
        
        return this.purchaseOrder.lines.reduce((total, line) => {
          return total + (line.subtotal || 0);
        }, 0);
      },
      
      async updateStatus() {
        if (!this.selectedStatus) return;
        
        this.isUpdatingStatus = true;
        
        try {
          await PurchaseOrderService.updatePurchaseOrderStatus(this.id, this.selectedStatus);
          
          // Refresh data
          await this.fetchPurchaseOrder();
          this.selectedStatus = '';
        } catch (error) {
          console.error('Error updating purchase order status:', error);
          
          if (error.response && error.response.data && error.response.data.message) {
            alert(error.response.data.message);
          } else {
            alert('Failed to update status. Please try again.');
          }
        } finally {
          this.isUpdatingStatus = false;
        }
      },
      
      confirmDelete() {
        this.showDeleteModal = true;
      },
      
      closeDeleteModal() {
        this.showDeleteModal = false;
      },
      
      async deletePurchaseOrder() {
        try {
          await PurchaseOrderService.deletePurchaseOrder(this.id);
          this.$router.push('/purchasing/orders');
        } catch (error) {
          console.error('Error deleting purchase order:', error);
          
          if (error.response && error.response.data && error.response.data.message) {
            alert(error.response.data.message);
          } else {
            alert('Failed to delete purchase order. Please try again.');
          }
          
          this.closeDeleteModal();
        }
      },
      
      printPO() {
        window.print();
      }
    },
    mounted() {
      this.fetchPurchaseOrder();
    }
  };
  </script>
  
  <style scoped>
  .po-detail-container {
    padding: 1rem;
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
  }
  
  .header-left {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-600);
    text-decoration: none;
    font-size: 0.875rem;
  }
  
  .back-link:hover {
    color: var(--primary-color);
  }
  
  .header-left h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--gray-800);
  }
  
  .header-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1rem;
  }
  
  .status-section {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .status-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .status-select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    background-color: white;
  }
  
  .action-buttons {
    display: flex;
    gap: 0.75rem;
  }
  
  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
  
  .loading-spinner {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  
  .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
  }
  
  .error-icon {
    font-size: 3rem;
    color: var(--danger-color);
    margin-bottom: 1rem;
  }
  
  .po-detail-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .detail-card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
  }
  
  .card-title {
    margin: 0;
    font-size: 1.25rem;
    color: var(--gray-800);
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  .info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .info-label {
    font-size: 0.875rem;
    color: var(--gray-500);
  }
  
  .info-value {
    font-size: 1rem;
    color: var(--gray-800);
    font-weight: 500;
  }
  
  .table-responsive {
    overflow-x: auto;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
  }
  
  .data-table th {
    padding: 0.75rem;
    text-align: left;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
    font-weight: 500;
    color: var(--gray-700);
  }
  
  .data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--gray-100);
  }
  
  .data-table tbody tr:hover td {
    background-color: var(--gray-50);
  }
  
  .data-table tfoot td {
    padding: 0.75rem;
    border-top: 1px solid var(--gray-200);
  }
  
  .text-right {
    text-align: right;
  }
  
  .grand-total {
    font-weight: 600;
    font-size: 1rem;
    color: var(--gray-900);
  }
  
  .item-name {
    font-weight: 500;
  }
  
  .item-code {
    font-size: 0.75rem;
    color: var(--gray-500);
  }
  
  .tabs-container {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .tabs-header {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    background-color: var(--gray-50);
  }
  
  .tab-button {
    padding: 1rem 1.5rem;
    border: none;
    background: none;
    color: var(--gray-600);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border-bottom: 2px solid transparent;
  }
  
  .tab-button:hover {
    color: var(--gray-800);
  }
  
  .tab-button.active {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
  }
  
  .tab-content {
    min-height: 200px;
  }
  
  .tab-pane {
    padding: 1.5rem;
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    text-align: center;
  }
  
  .empty-icon {
    font-size: 2.5rem;
    color: var(--gray-300);
    margin-bottom: 1rem;
  }
  
  .empty-state h3 {
    font-size: 1.125rem;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
  }
  
  .empty-state p {
    color: var(--gray-500);
    max-width: 24rem;
  }
  
  .empty-actions {
    margin-top: 1.5rem;
  }
  
  .status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  .status-draft {
    background-color: var(--gray-100);
    color: var(--gray-700);
  }
  
  .status-pending {
    background-color: #fef3c7;
    color: #92400e;
  }
  
  .status-approved {
    background-color: #dcfce7;
    color: #166534;
  }
  
  .status-sent {
    background-color: #dbeafe;
    color: #1e40af;
  }
  
  .status-partial {
    background-color: #fef9c3;
    color: #854d0e;
  }
  
  .status-received {
    background-color: #d1fae5;
    color: #065f46;
  }
  
  .status-completed {
    background-color: #bbf7d0;
    color: #15803d;
  }
  
  .status-canceled {
    background-color: #fee2e2;
    color: #b91c1c;
  }
  
  .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    color: var(--primary-color);
    transition: background-color 0.2s;
  }
  
  .action-btn:hover {
    background-color: var(--primary-bg);
  }
  
  .btn {
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: none;
    transition: background-color 0.2s, color 0.2s;
  }
  
  .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover:not(:disabled) {
    background-color: var(--primary-dark);
  }
  
  .btn-primary:disabled {
    background-color: #93c5fd;
    cursor: not-allowed;
  }
  
  .btn-secondary {
    background-color: #e2e8f0;
    color: #1e293b;
  }
  
  .btn-secondary:hover {
    background-color: #cbd5e1;
  }
  
  .btn-danger {
    background-color: var(--danger-color);
    color: white;
  }
  
  .btn-danger:hover {
    background-color: #b91c1c;
  }
  
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
  }
  
  .modal-container {
    max-width: 500px;
    width: 100%;
    margin: 0 1rem;
  }
  
  .modal-content {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
  }
  
  .modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--gray-800);
  }
  
  .modal-close-btn {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0.25rem;
  }
  
  .modal-close-btn:hover {
    color: var(--gray-700);
  }
  
  .modal-body {
    padding: 1.5rem;
    color: var(--gray-700);
  }
  
  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
  }
  
  @media (max-width: 768px) {
    .page-header {
      flex-direction: column;
      gap: 1rem;
    }
  
    .header-actions {
      align-items: flex-start;
    }
  
    .info-grid {
      grid-template-columns: 1fr;
    }
  }
  
  @media print {
    .back-link,
    .header-actions,
    .tabs-header {
      display: none !important;
    }
  
    .po-detail-container {
      padding: 0;
    }
  
    .detail-card,
    .tabs-container {
      box-shadow: none;
      margin-bottom: 2rem;
    }
  
    .card-header {
      background-color: white;
      border-bottom: 2px solid #000;
    }
  
    .data-table th {
      background-color: white;
      border-bottom: 1px solid #000;
    }
  
    .data-table td {
      border-bottom: 1px solid #ddd;
    }
  }
  </style>