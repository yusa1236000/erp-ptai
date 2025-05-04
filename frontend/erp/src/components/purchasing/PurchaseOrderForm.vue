<!-- src/components/purchasing/PurchaseOrderForm.vue -->
<template>
    <div class="po-form">
      <form @submit.prevent="submitForm">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Purchase Order Information</h3>
          </div>
          <div class="card-body">
            <div class="grid-2">
              <div class="form-group">
                <label for="vendor">Vendor <span class="required">*</span></label>
                <select
                  id="vendor"
                  v-model="form.vendor_id"
                  class="form-control"
                  :class="{ 'is-invalid': errors.vendor_id }"
                  required
                  @change="handleVendorChange"
                >
                  <option value="" disabled>Select Vendor</option>
                  <option
                    v-for="vendor in vendors"
                    :key="vendor.vendor_id"
                    :value="vendor.vendor_id"
                  >
                    {{ vendor.name }}
                  </option>
                </select>
                <div class="invalid-feedback" v-if="errors.vendor_id">
                  {{ errors.vendor_id }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="po_date">PO Date <span class="required">*</span></label>
                <input
                  id="po_date"
                  type="date"
                  v-model="form.po_date"
                  class="form-control"
                  :class="{ 'is-invalid': errors.po_date }"
                  required
                />
                <div class="invalid-feedback" v-if="errors.po_date">
                  {{ errors.po_date }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="payment_terms">Payment Terms</label>
                <input
                  id="payment_terms"
                  type="text"
                  v-model="form.payment_terms"
                  class="form-control"
                  :class="{ 'is-invalid': errors.payment_terms }"
                  placeholder="e.g., Net 30, COD"
                />
                <div class="invalid-feedback" v-if="errors.payment_terms">
                  {{ errors.payment_terms }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="delivery_terms">Delivery Terms</label>
                <input
                  id="delivery_terms"
                  type="text"
                  v-model="form.delivery_terms"
                  class="form-control"
                  :class="{ 'is-invalid': errors.delivery_terms }"
                  placeholder="e.g., FOB, CIF"
                />
                <div class="invalid-feedback" v-if="errors.delivery_terms">
                  {{ errors.delivery_terms }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="expected_delivery">Expected Delivery Date</label>
                <input
                  id="expected_delivery"
                  type="date"
                  v-model="form.expected_delivery"
                  class="form-control"
                  :class="{ 'is-invalid': errors.expected_delivery }"
                />
                <div class="invalid-feedback" v-if="errors.expected_delivery">
                  {{ errors.expected_delivery }}
                </div>
              </div>
  
              <div class="form-group">
                <label for="currency_code">Currency</label>
                <select
                  id="currency_code"
                  v-model="form.currency_code"
                  class="form-control"
                  :class="{ 'is-invalid': errors.currency_code }"
                >
                  <option value="USD">USD - US Dollar</option>
                  <option value="EUR">EUR - Euro</option>
                  <option value="GBP">GBP - British Pound</option>
                  <option value="JPY">JPY - Japanese Yen</option>
                  <option value="IDR">IDR - Indonesian Rupiah</option>
                  <!-- Add more currencies as needed -->
                </select>
                <div class="invalid-feedback" v-if="errors.currency_code">
                  {{ errors.currency_code }}
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Line Items Section -->
        <div class="card mt-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Line Items</h3>
            <button
              type="button"
              class="btn btn-sm btn-primary"
              @click="addNewLine"
            >
              <i class="fas fa-plus"></i> Add Item
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th style="width: 40%">Item</th>
                    <th style="width: 15%">Quantity</th>
                    <th style="width: 15%">UOM</th>
                    <th style="width: 15%">Unit Price</th>
                    <th style="width: 10%">Tax</th>
                    <th style="width: 5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(line, index) in form.lines" :key="index">
                    <td>
                      <input
                        type="text"
                        v-model="line.itemSearch"
                        class="form-control mb-1"
                        placeholder="Search item..."
                      />
                      <select
                        v-model="line.item_id"
                        class="form-control"
                        :class="{ 'is-invalid': getLineError(index, 'item_id') }"
                        required
                        @change="handleItemChange(index)"
                      >
                        <option value="" disabled>Select Item</option>
                        <option
                          v-for="item in filteredItems(line.itemSearch)"
                          :key="item.item_id"
                          :value="item.item_id"
                        >
                          {{ item.name }} ({{ item.item_code }})
                        </option>
                      </select>
                      <div class="invalid-feedback" v-if="getLineError(index, 'item_id')">
                        {{ getLineError(index, 'item_id') }}
                      </div>
                    </td>
                    <td>
                      <input
                        type="number"
                        v-model="line.quantity"
                        class="form-control"
                        :class="{ 'is-invalid': getLineError(index, 'quantity') }"
                        min="0.01"
                        step="0.01"
                        required
                        @change="updateLineTotals(index)"
                      />
                      <div class="invalid-feedback" v-if="getLineError(index, 'quantity')">
                        {{ getLineError(index, 'quantity') }}
                      </div>
                    </td>
                    <td>
                      <select
                        v-model="line.uom_id"
                        class="form-control"
                        :class="{ 'is-invalid': getLineError(index, 'uom_id') }"
                        required
                      >
                        <option value="" disabled>Select UOM</option>
                        <option
                          v-for="uom in unitsOfMeasure"
                          :key="uom.uom_id"
                          :value="uom.uom_id"
                        >
                          {{ uom.name }}
                        </option>
                      </select>
                      <div class="invalid-feedback" v-if="getLineError(index, 'uom_id')">
                        {{ getLineError(index, 'uom_id') }}
                      </div>
                    </td>
                    <td>
                      <input
                        type="number"
                        v-model="line.unit_price"
                        class="form-control"
                        :class="{ 'is-invalid': getLineError(index, 'unit_price') }"
                        min="0.01"
                        step="0.01"
                        required
                        @change="updateLineTotals(index)"
                      />
                      <div class="invalid-feedback" v-if="getLineError(index, 'unit_price')">
                        {{ getLineError(index, 'unit_price') }}
                      </div>
                    </td>
                    <td>
                      <input
                        type="number"
                        v-model="line.tax"
                        class="form-control"
                        :class="{ 'is-invalid': getLineError(index, 'tax') }"
                        min="0"
                        step="0.01"
                        @change="updateLineTotals(index)"
                      />
                      <div class="invalid-feedback" v-if="getLineError(index, 'tax')">
                        {{ getLineError(index, 'tax') }}
                      </div>
                    </td>
                    <td>
                      <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        @click="removeLine(index)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="form.lines.length === 0">
                    <td colspan="6" class="text-center">
                      No items added yet. Click "Add Item" to add a line item.
                    </td>
                  </tr>
                </tbody>
                <tfoot v-if="form.lines.length > 0">
                  <tr>
                    <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                    <td colspan="2">{{ formatCurrency(calculateSubtotal()) }}</td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right"><strong>Tax Total:</strong></td>
                    <td colspan="2">{{ formatCurrency(calculateTaxTotal()) }}</td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                    <td colspan="2" class="grand-total">{{ formatCurrency(calculateGrandTotal()) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
  
        <!-- Form Actions -->
        <div class="form-actions mt-4">
          <button
            type="button"
            class="btn btn-secondary"
            @click="cancel"
            :disabled="isSubmitting"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary ml-2"
            :disabled="isSubmitting || form.lines.length === 0"
          >
            {{ isSubmitting ? 'Saving...' : isEditMode ? 'Update' : 'Create' }} Purchase Order
          </button>
        </div>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    name: 'PurchaseOrderForm',
    props: {
      purchaseOrder: {
        type: Object,
        default: null
      },
      isEditMode: {
        type: Boolean,
        default: false
      },
      isSubmitting: {
        type: Boolean,
        default: false
      },
      vendors: {
        type: Array,
        default: () => []
      },
      items: {
        type: Array,
        default: () => []
      },
      unitsOfMeasure: {
        type: Array,
        default: () => []
      },
      serverErrors: {
        type: Object,
        default: () => ({})
      }
    },
    data() {
      return {
        form: {
          vendor_id: '',
          po_date: new Date().toISOString().substr(0, 10),
          payment_terms: '',
          delivery_terms: '',
          expected_delivery: '',
          currency_code: 'USD',
          lines: []
        },
        errors: {},
        lineErrors: []
      };
    },
    watch: {
      purchaseOrder: {
        handler(newValue) {
          if (newValue) {
            this.populateForm();
          }
        },
        immediate: true
      },
      serverErrors: {
        handler(newValue) {
          this.handleServerErrors(newValue);
        },
        deep: true
      }
    },
    methods: {
      populateForm() {
        if (!this.purchaseOrder) return;
  
        // Defensive check for vendor_id
        this.form.vendor_id = this.purchaseOrder.vendor_id || '';
        this.form.po_date = this.purchaseOrder.po_date;
        this.form.payment_terms = this.purchaseOrder.payment_terms;
        this.form.delivery_terms = this.purchaseOrder.delivery_terms;
        this.form.expected_delivery = this.purchaseOrder.expected_delivery;
        this.form.currency_code = this.purchaseOrder.currency_code || 'USD';
  
        // Copy line items
        this.form.lines = this.purchaseOrder.lines 
          ? this.purchaseOrder.lines.map(line => ({
              item_id: line.item_id,
              quantity: line.quantity,
              uom_id: line.uom_id,
              unit_price: line.unit_price,
              tax: line.tax || 0,
              subtotal: line.subtotal,
              total: line.total,
              itemSearch: '' // add itemSearch property for search input
            }))
          : [];
      },
  
      addNewLine() {
        this.form.lines.push({
          item_id: '',
          quantity: 1,
          uom_id: '',
          unit_price: 0,
          tax: 0,
          subtotal: 0,
          total: 0,
          itemSearch: '' // add itemSearch property for search input
        });
        this.lineErrors.push({});
      },
  
      removeLine(index) {
        this.form.lines.splice(index, 1);
        this.lineErrors.splice(index, 1);
      },
  
      handleVendorChange() {
        // Can be used to load vendor-specific data like payment terms or preferred currency
        const selectedVendor = this.vendors.find(v => v.vendor_id === this.form.vendor_id);
        if (selectedVendor && selectedVendor.preferred_currency) {
          this.form.currency_code = selectedVendor.preferred_currency;
        }
      },
  
      handleItemChange(index) {
        const line = this.form.lines[index];
        const selectedItem = this.items.find(i => i.item_id === line.item_id);
  
        if (selectedItem) {
          // Set default UOM if available and not already set
          if (selectedItem.uom_id && !line.uom_id) {
            line.uom_id = selectedItem.uom_id;
          }
  
          // Set default unit price from cost price if available
          if (selectedItem.cost_price && line.unit_price === 0) {
            line.unit_price = selectedItem.cost_price;
            this.updateLineTotals(index);
          }
        }
        // Clear the search input after item selection
        line.itemSearch = '';
      },
  
      updateLineTotals(index) {
        const line = this.form.lines[index];
        if (!line) return;
  
        // Calculate subtotal and total
        line.subtotal = parseFloat(line.quantity) * parseFloat(line.unit_price) || 0;
        line.total = line.subtotal + parseFloat(line.tax || 0);
      },
  
      calculateSubtotal() {
        return this.form.lines.reduce((total, line) => {
          return total + (parseFloat(line.quantity) * parseFloat(line.unit_price) || 0);
        }, 0);
      },
  
      calculateTaxTotal() {
        return this.form.lines.reduce((total, line) => {
          return total + (parseFloat(line.tax) || 0);
        }, 0);
      },
  
      calculateGrandTotal() {
        return this.calculateSubtotal() + this.calculateTaxTotal();
      },
  
      formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: this.form.currency_code || 'USD'
        }).format(amount);
      },
  
      getLineError(index, field) {
        return this.lineErrors[index] && this.lineErrors[index][field];
      },
  
      handleServerErrors(errors) {
        // Handle general form errors
        this.errors = {};
        this.lineErrors = Array(this.form.lines.length).fill({});
  
        for (const key in errors) {
          if (key.startsWith('lines.')) {
            // Handle line item errors
            const parts = key.split('.');
            const lineIndex = parseInt(parts[1]);
            const fieldName = parts[2];
  
            if (!this.lineErrors[lineIndex]) {
              this.lineErrors[lineIndex] = {};
            }
            this.lineErrors[lineIndex][fieldName] = errors[key][0];
          } else {
            // Handle main form errors
            this.errors[key] = errors[key][0];
          }
        }
      },
  
      validateForm() {
        this.errors = {};
        this.lineErrors = Array(this.form.lines.length).fill().map(() => ({}));
  
        let isValid = true;
  
        // Validate main fields
        if (!this.form.vendor_id) {
          this.errors.vendor_id = 'Vendor is required';
          isValid = false;
        }
  
        if (!this.form.po_date) {
          this.errors.po_date = 'PO Date is required';
          isValid = false;
        }
  
        // Validate line items
        if (this.form.lines.length === 0) {
          isValid = false;
        }
  
        this.form.lines.forEach((line, index) => {
          if (!line.item_id) {
            this.lineErrors[index].item_id = 'Item is required';
            isValid = false;
          }
  
          if (!line.quantity || line.quantity <= 0) {
            this.lineErrors[index].quantity = 'Quantity must be greater than 0';
            isValid = false;
          }
  
          if (!line.uom_id) {
            this.lineErrors[index].uom_id = 'UOM is required';
            isValid = false;
          }
  
          if (!line.unit_price || line.unit_price <= 0) {
            this.lineErrors[index].unit_price = 'Unit price must be greater than 0';
            isValid = false;
          }
        });
  
        return isValid;
      },
  
      submitForm() {
        if (!this.validateForm()) {
          return;
        }
  
        // Final preparation of form data
        const formData = { ...this.form };
  
        // Update line totals before submitting
        formData.lines.forEach((line, index) => {
          this.updateLineTotals(index);
        });
  
        // Emit form data to parent
        this.$emit('submit', formData);
      },
  
      cancel() {
        this.$emit('cancel');
      }
    },
    // Move filteredItems inside methods
  methods: {
    filteredItems(searchTerm) {
      if (!searchTerm) {
        return this.items;
      }
      const lowerSearch = searchTerm.toLowerCase();
      return this.items.filter(item => {
        return (
          (item.name && item.name.toLowerCase().includes(lowerSearch)) ||
          (item.item_code && item.item_code.toLowerCase().includes(lowerSearch))
        );
      });
    }
  }
  };
  </script>
  
  <style scoped>
  .po-form {
    max-width: 100%;
  }
  
  .card {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .card-header {
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
  
  .grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
  }
  
  .required {
    color: var(--danger-color);
  }
  
  .form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: var(--gray-700);
    background-color: #fff;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out;
  }
  
  .form-control:focus {
    border-color: var(--primary-color);
    outline: 0;
  }
  
  .form-control.is-invalid {
    border-color: var(--danger-color);
  }
  
  .invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: var(--danger-color);
  }
  
  .table {
    width: 100%;
    margin-bottom: 1rem;
    color: var(--gray-700);
    border-collapse: collapse;
  }
  
  .table th,
  .table td {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid var(--gray-200);
  }
  
  .table th {
    background-color: var(--gray-50);
    font-weight: 500;
    text-align: left;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1.5rem;
  }
  
  .btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
    border: none;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }
  
  .btn-primary:hover:not(:disabled) {
    background-color: var(--primary-dark);
  }
  
  .btn-secondary {
    background-color: var(--gray-200);
    color: var(--gray-700);
  }
  
  .btn-secondary:hover:not(:disabled) {
    background-color: var(--gray-300);
  }
  
  .btn-danger {
    background-color: var(--danger-color);
    color: white;
  }
  
  .btn-danger:hover:not(:disabled) {
    background-color: #b91c1c;
  }
  
  .btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
  }
  
  .ml-2 {
    margin-left: 0.5rem;
  }
  
  .mt-4 {
    margin-top: 1rem;
  }
  
  .text-right {
    text-align: right;
  }
  
  .text-center {
    text-align: center;
  }
  
  .grand-total {
    font-weight: bold;
    font-size: 1.1rem;
    color: var(--gray-900);
  }
  
  @media (max-width: 768px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }
  }
  </style>