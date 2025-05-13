<!-- src/views/sales/SalesInvoicePrint.vue -->
<template>
  <div class="print-container">
    <!-- Print Controls (visible on screen only) -->
    <div class="print-controls no-print">
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
      </button>
      <button @click="printInvoice" class="btn btn-primary">
        <i class="fas fa-print"></i> Print Invoice
      </button>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading" class="loading-indicator no-print">
      <i class="fas fa-spinner fa-spin"></i> Loading invoice data...
    </div>

    <!-- Invoice Content (for printing) -->
    <div v-else class="invoice-document">
      <!-- Invoice Header -->
      <div class="header">
        <div class="company-info">
          <h1 class="company-name">PT. ARMSTRONG INDUSTRI INDONESIA</h1>
          <p>EJIP INDUSTRIAL PARK PLOT 1 A-3 LIPPO CIKARANG</p>
          <p>SUKARESMI, CIKARANG SELATAN, BEKASI 17857</p>
        </div>
        <div class="invoice-info">
          <div class="info-row">
            <span>Page : 1 of 1</span>
          </div>
          <div class="info-row">
            <span>No. Inv. : <strong>{{ invoice.invoice_number }}</strong></span>
          </div>
          <div class="info-row">
            <span>Date. Inv. : {{ formatDateSimple(invoice.invoice_date) }}</span>
          </div>
        </div>
      </div>

      <!-- Customer Information -->
      <div class="customer-section">
        <div class="customer-block">
          <div class="customer-title">SOLD TO :</div>
          <div v-if="invoice.customer" class="customer-details">
            {{ invoice.customer.name }}<br>
            {{ invoice.customer.address }}<br>
            {{ invoice.customer.city }} {{ invoice.customer.country }} {{ invoice.customer.postal_code }}
          </div>
          <div v-else class="customer-details">
            PT. INDONESIA EPSON INDUSTRY<br>
            EJIP INDUSTRIAL PARK PLOT 4E<br>
            CIKARANG SELATAN<br>
            BEKASI INDONESIA 17550
          </div>
        </div>
        <div class="customer-block">
          <div class="customer-title">GOOD DELIVERY TO :</div>
          <div v-if="invoice.customer" class="customer-details">
            {{ invoice.customer.name }}<br>
            {{ invoice.customer.address }}<br>
            {{ invoice.customer.city }} {{ invoice.customer.country }} {{ invoice.customer.postal_code }}
          </div>
          <div v-else class="customer-details">
            PT. INDONESIA EPSON INDUSTRY<br>
            EJIP INDUSTRIAL PARK PLOT 4E<br>
            CIKARANG SELATAN<br>
            BEKASI INDONESIA 17550
          </div>
        </div>
      </div>

      <!-- Order Information -->
      <div class="order-info">
        <table class="order-table">
          <tbody>
            <tr>
              <th>PO No.</th>
              <th>PO Date</th>
              <th>SALES PERSON</th>
              <th>SHIP VIA</th>
              <th>TERMS</th>
              <th>DO No.</th>
            </tr>
            <tr>
              <td>{{ invoice.reference || '52042-INLAND' }}</td>
              <td>{{ formatDateSimple(invoice.invoice_date) }}</td>
              <td>DELLA</td>
              <td></td>
              <td>{{ invoice.payment_terms || 'Net 60 days' }}</td>
              <td>{{ invoice.delivery ? invoice.delivery.delivery_number : 'B24-038844' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- CO Number -->
      <div class="co-number">
        <span>CO-NUM : {{ invoice.delivery ? invoice.delivery.delivery_number : 'SO3-24-010837' }}</span>
      </div>

      <!-- Part Info -->
      <div class="part-info">
        <div class="part-number-col">PART NUMBER</div>
        <div class="do-remarks-col">DO REMARKS</div>
      </div>

      <!-- Invoice Items -->
      <table class="items-table">
        <thead>
          <tr>
            <th class="no-col">NO.</th>
            <th class="part-col">PART NUMBER</th>
            <th class="do-col">DO No.</th>
            <th class="qty-col right">Qty SHIPPED</th>
            <th class="uom-col">UOM</th>
            <th class="desc-col">Description</th>
            <th class="unit-price-col right">UNIT PRICE</th>
            <th class="amount-col right">AMOUNT</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(line, index) in invoiceLines" :key="line.line_id || index">
            <td class="no-col center">{{ index + 1 }}</td>
            <td class="part-col">{{ line.item ? line.item.item_code : '5168816' }}</td>
            <td class="do-col">{{ invoice.delivery ? invoice.delivery.delivery_number : 'B24-038844' }}</td>
            <td class="qty-col right">{{ formatNumberWithCommas(line.quantity) }}</td>
            <td class="uom-col">{{ line.uom_id || 'UNIT' }}</td>
            <td class="desc-col">{{ line.item ? line.item.name : 'PAD SET PRINTER' }}</td>
            <td class="unit-price-col right">{{ formatNumberWithCommas(line.unit_price) }}</td>
            <td class="amount-col right">{{ formatNumberWithCommas(line.total) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Invoice Summary -->
      <div class="invoice-summary">
        <div class="summary-row">
          <div class="summary-label">Harga jual</div>
          <div class="summary-colon">:</div>
          <div class="summary-value">{{ formatNumberWithCommas(calculateSubtotal()) }}</div>
        </div>
        <div class="summary-row">
          <div class="summary-label">Tax</div>
          <div class="summary-colon">:</div>
          <div class="summary-value">{{ invoice.tax_amount > 0 ? (getTaxRate() + '%') : '0 %' }} {{ formatNumberWithCommas(invoice.tax_amount || 0) }}</div>
        </div>
        <div class="summary-row">
          <div class="summary-label">TOTAL</div>
          <div class="summary-colon">:</div>
          <div class="summary-value">{{ formatNumberWithCommas(invoice.total_amount) }}</div>
        </div>
      </div>

      <!-- Payment Terms -->
      <div class="payment-terms">
        <h3>Terms of Payment :</h3>
        <ol>
          <li>Payment through bank is treated as settled only after clearing</li>
          <li>Interest for late payment rate 5% per month</li>
          <li>Bank DBS Indonesia<br>
              DBS Tower, 35 th floor Ciputra Word I Jakarta,<br>
              Jl. Prof Dr. Satrio Kav. 3-5 Karet Kuningan<br>
              Account No. : 030-16069-48 (IDR)<br>
              PT. ARMSTRONG INDUSTRI INDONESIA<br>
              Account No. : 030-16068-47 (USD)<br>
              Jakarta Selatan (12940)
          </li>
        </ol>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SalesInvoicePrint',
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      invoice: {},
      receivable: null,
      loading: true,
    };
  },
  computed: {
    invoiceLines() {
      return this.invoice.sales_invoice_lines || [];
    }
  },
  mounted() {
    this.fetchInvoice();
  },
  methods: {
    async fetchInvoice() {
      this.loading = true;
      try {
        const response = await axios.get(`/invoices/${this.id}`);
        this.invoice = response.data.data;

        // Get receivable information
        if (this.invoice.customerReceivables && this.invoice.customerReceivables.length > 0) {
          this.receivable = this.invoice.customerReceivables[0];
        }
      } catch (error) {
        console.error('Error fetching invoice:', error);
        this.$toast.error('Failed to load invoice details');
      } finally {
        this.loading = false;
      }
    },
    formatDateSimple(dateString) {
      if (!dateString) return '26/12/2024';
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    },
    formatNumberWithCommas(value) {
      if (value === undefined || value === null) return '$0.00000';
      const number = parseFloat(value);
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 5,
        maximumFractionDigits: 5
      }).format(number);
    },
    calculateSubtotal() {
      return this.invoiceLines.reduce((total, line) => total + (line.subtotal || 0), 0);
    },
    getTaxRate() {
      // Calculate the tax rate as a percentage of the subtotal
      const subtotal = this.calculateSubtotal();
      if (subtotal === 0 || !this.invoice.tax_amount) return 0;
      return Math.round((this.invoice.tax_amount / subtotal) * 100);
    },
    printInvoice() {
      window.print();
    },
    goBack() {
      this.$router.go(-1);
    }
  }
};
</script>

<style>
/* Reset and general styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  font-size: 12px;
  line-height: 1.4;
}

.print-container {
  max-width: 210mm; /* A4 width */
  margin: 0 auto;
  padding: 1rem;
  background: white;
}

/* Print controls */
.print-controls {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  font-weight: bold;
}

.btn i {
  margin-right: 0.5rem;
}

.btn-primary {
  background: #2563eb;
  color: white;
  border: none;
}

.btn-secondary {
  background: #e5e7eb;
  color: #1f2937;
  border: none;
}

.loading-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.loading-indicator i {
  margin-right: 0.5rem;
}

/* Invoice document */
.invoice-document {
  line-height: 1.3;
}

/* Header section */
.header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.company-name {
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 0.25rem;
}

.company-info p {
  font-size: 11px;
  margin: 0;
}

.invoice-info {
  text-align: right;
  font-size: 11px;
}

.info-row {
  margin-bottom: 0.125rem;
}

/* Customer section */
.customer-section {
  display: flex;
  border: 1px solid black;
}

.customer-block {
  flex: 1;
  padding: 0.5rem;
}

.customer-block:first-child {
  border-right: 1px solid black;
}

.customer-title {
  font-weight: bold;
  margin-bottom: 0.25rem;
}

.customer-details {
  font-size: 11px;
}

/* Order info section */
.order-info {
  margin-top: -1px;
}

.order-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid black;
}

.order-table th, .order-table td {
  border: 1px solid black;
  padding: 0.25rem 0.5rem;
  font-size: 11px;
}

.order-table th {
  font-weight: bold;
}

/* CO Number */
.co-number {
  border-left: 1px solid black;
  border-right: 1px solid black;
  padding: 0.25rem 0.5rem;
  font-weight: bold;
  font-size: 11px;
  margin-top: -1px;
}

/* Part info */
.part-info {
  display: flex;
  border-left: 1px solid black;
  border-right: 1px solid black;
  border-bottom: 1px solid black;
  margin-top: -1px;
}

.part-number-col, .do-remarks-col {
  flex: 1;
  padding: 0.25rem 0.5rem;
  font-weight: bold;
  font-size: 11px;
}

/* Items table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid black;
  margin-top: 1rem;
}

.items-table th, .items-table td {
  border: 1px solid black;
  padding: 0.25rem 0.5rem;
  font-size: 11px;
}

.no-col { width: 5%; }
.part-col { width: 12%; }
.do-col { width: 12%; }
.qty-col { width: 10%; }
.uom-col { width: 8%; }
.desc-col { width: 25%; }
.unit-price-col { width: 14%; }
.amount-col { width: 14%; }

.center { text-align: center; }
.right { text-align: right; }

/* Invoice summary */
.invoice-summary {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-top: 0.5rem;
  width: 100%;
}

.summary-row {
  display: flex;
  margin-bottom: 0.25rem;
  width: 260px;
}

.summary-label {
  width: 80px;
}

.summary-colon {
  width: 10px;
}

.summary-value {
  flex: 1;
  text-align: right;
}

/* Payment terms */
.payment-terms {
  margin-top: 2rem;
}

.payment-terms h3 {
  font-size: 12px;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.payment-terms ol {
  padding-left: 1.5rem;
}

.payment-terms li {
  margin-bottom: 0.25rem;
  font-size: 11px;
}

/* Print specific styles */
@media print {
  .no-print {
    display: none !important;
  }

  .print-container {
    padding: 0;
  }

  @page {
    margin: 0.5cm;
  }
}
</style>
