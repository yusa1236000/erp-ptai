<!-- src/views/purchasing/VendorInvoicePrint.vue -->
<template>
  <div class="invoice-print-container">
    <!-- Print Header Controls (visible on screen, hidden when printing) -->
    <div class="print-controls">
      <button @click="$router.push(`/purchasing/vendor-invoices/${$route.params.id}`)" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Invoice
      </button>
      <div class="print-buttons">
        <button @click="printInvoice" class="btn btn-primary">
          <i class="fas fa-print"></i> Print Invoice
        </button>
        <button @click="printInvoicePDF" class="btn btn-danger">
          <i class="fas fa-file-pdf"></i> Print PDF
        </button>
      </div>
    </div>

    <!-- Printable Invoice Content -->
    <div class="printable-area">
      <div v-if="loading" class="loading">
        <i class="fas fa-spinner fa-spin"></i> Loading invoice data...
      </div>

      <div v-else-if="!invoice" class="error-message">
        Invoice not found or has been deleted.
      </div>

      <div v-else class="invoice-document">
        <!-- Invoice Header -->
        <div class="invoice-header">
          <div class="company-info">
            <h3>PT. ARMSTRONG INDUSTRI INDONESIA</h3>
            <p>EJIP Industrial Park Plot 1 A-3</p>
            <p>Desa Sukaresmi, Cikarang Selatan</p>
            <p>TBekasi 17550, Indonesia</p>
          </div>
          <div class="invoice-title">
            <h3>VENDOR INVOICE</h3>
            <div class="invoice-number">{{ invoice.invoice_number }}</div>
          </div>
        </div>

        <!-- Invoice Status Banner -->
        <!-- <div class="invoice-status-banner" :class="getStatusClass(invoice.status)">
          {{ capitalizeFirst(invoice.status) }}
        </div> -->

        <!-- Vendor & Invoice Details -->
        <div class="invoice-details-section">
          <div class="vendor-details">
            <h3>Vendor</h3>
            <div class="details-box">
              <p class="vendor-name">{{ invoice.vendor?.name }}</p>
              <p>{{ invoice.vendor?.address }}</p>
              <p>{{ invoice.vendor?.city }}, {{ invoice.vendor?.state }} {{ invoice.vendor?.postal_code }}</p>
              <p>{{ invoice.vendor?.country }}</p>
              <p v-if="invoice.vendor?.phone">Phone: {{ invoice.vendor?.phone }}</p>
              <p v-if="invoice.vendor?.email">Email: {{ invoice.vendor?.email }}</p>
              <p v-if="invoice.vendor?.tax_id">Tax ID: {{ invoice.vendor?.tax_id }}</p>
            </div>
          </div>

          <div class="invoice-details">
            <h3>Invoice Details</h3>
            <div class="details-box">
              <table class="details-table">
                <tr>
                  <th>Invoice Date:</th>
                  <td>{{ formatDate(invoice.invoice_date) }}</td>
                </tr>
                <tr>
                  <th>Due Date:</th>
                  <td>{{ formatDate(invoice.due_date) }}</td>
                </tr>
                <tr v-if="invoice.currency_code !== 'USD'">
                  <th>Currency:</th>
                  <td>{{ invoice.currency_code }}</td>
                </tr>
                <tr v-if="invoice.currency_code !== 'USD'">
                  <th>Exchange Rate:</th>
                  <td>{{ invoice.exchange_rate }} (to USD)</td>
                </tr>
                <tr v-if="invoice.po_id">
                  <th>PO Reference:</th>
                  <td>{{ invoice.purchaseOrder?.po_number || 'Multiple POs' }}</td>
                </tr>
                <tr>
                  <th>Payment Status:</th>
                  <td>{{ capitalizeFirst(invoice.status) }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Invoice Items -->
        <div class="invoice-items-section">
          <h3>Invoice Items</h3>
          <table class="items-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Description</th>
                <th>PO Number</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Tax</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in invoice.lines" :key="line.line_id">
                <td>{{ line.item?.item_code || 'N/A' }}</td>
                <td>{{ line.item?.name || 'N/A' }}</td>
                <td>{{ line.purchaseOrderLine?.purchaseOrder?.po_number || 'N/A' }}</td>
                <td>{{ line.quantity }}</td>
                <td>{{ formatCurrency(line.unit_price, invoice.currency_code) }}</td>
                <td>{{ formatCurrency(line.subtotal, invoice.currency_code) }}</td>
                <td>{{ formatCurrency(line.tax, invoice.currency_code) }}</td>
                <td>{{ formatCurrency(line.total, invoice.currency_code) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" class="text-right"><strong>Totals:</strong></td>
                <td>{{ formatCurrency(invoice.total_amount - invoice.tax_amount, invoice.currency_code) }}</td>
                <td>{{ formatCurrency(invoice.tax_amount, invoice.currency_code) }}</td>
                <td>{{ formatCurrency(invoice.total_amount, invoice.currency_code) }}</td>
              </tr>
              <tr v-if="invoice.currency_code !== 'USD'">
                <td colspan="5" class="text-right"><strong>USD Equivalent:</strong></td>
                <td>{{ formatCurrency(invoice.base_currency_total - invoice.base_currency_tax, 'USD') }}</td>
                <td>{{ formatCurrency(invoice.base_currency_tax, 'USD') }}</td>
                <td>{{ formatCurrency(invoice.base_currency_total, 'USD') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Payment Information (if paid) -->
        <div class="payment-section" v-if="invoice.status === 'paid'">
          <h3>Payment Information</h3>
          <div class="details-box">
            <table class="details-table">
              <tr>
                <th>Payment Date:</th>
                <td>{{ formatDate(invoice.payment_date) }}</td>
              </tr>
              <tr>
                <th>Payment Method:</th>
                <td>{{ invoice.payment_method || 'N/A' }}</td>
              </tr>
              <tr v-if="invoice.payment_reference">
                <th>Reference:</th>
                <td>{{ invoice.payment_reference }}</td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Notes/Terms Section -->
        <div class="notes-section">
          <h3>Notes & Terms</h3>
          <div class="details-box">
            <p>Please include the invoice number when making payment. All payments are due within terms indicated above.</p>
            <p>For any questions regarding this invoice, please contact your purchasing manager or accounts payable department.</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
          <div class="footer-signature">
            <div class="signature-line">Approved By: _________________________</div>
            <div class="signature-line">Date: _________________________</div>
          </div>
          <div class="footer-copy">
            <p>Invoice generated on {{ formatDate(new Date()) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import html2pdf from 'html2pdf.js';

export default {
  name: 'VendorInvoicePrint',
  data() {
    return {
      loading: true,
      invoice: null,
      receiptDetails: []
    };
  },
  created() {
    this.loadInvoice();
  },
  methods: {
    async loadInvoice() {
      try {
        const invoiceId = this.$route.params.id;
        const response = await axios.get(`/vendor-invoices/${invoiceId}`);

        if (response.data.status === 'success') {
          this.invoice = response.data.data.invoice;
          this.receiptDetails = response.data.data.receipt_details || [];
        }
      } catch (error) {
        console.error('Error loading invoice:', error);
      } finally {
        this.loading = false;
      }
    },
    printInvoice() {
      const printContents = this.$el.querySelector('.printable-area').innerHTML;
      const printWindow = window.open('', '', 'height=600,width=800');
      printWindow.document.write('<html><head><title>Print Invoice</title>');
      // Include styles for print
      const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
        .map(style => style.outerHTML)
        .join('');
      printWindow.document.write(styles);
      printWindow.document.write('</head><body>');
      printWindow.document.write(printContents);
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    },
    printInvoicePDF() {
      const element = this.$el.querySelector('.printable-area');
      const opt = {
        margin:       0.5,
        filename:     `VendorInvoice_${this.invoice ? this.invoice.invoice_number : 'unknown'}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(element).save();
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    formatCurrency(amount, currency) {
      if (amount === null || amount === undefined) return 'N/A';

      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || 'USD',
        minimumFractionDigits: 2
      }).format(amount);
    },
    getStatusClass(status) {
      const statusClasses = {
        pending: 'status-pending',
        approved: 'status-approved',
        paid: 'status-paid',
        cancelled: 'status-cancelled'
      };

      return statusClasses[status] || '';
    },
    capitalizeFirst(str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  }
};
</script>

<style>
/* General styles */
.invoice-print-container {
  font-family: 'Inter', sans-serif;
  font-size: 12pt;
  color: #333;
  max-width: 210mm; /* A4 width */
  margin: 0 auto;
  padding: 1rem;
}

.print-controls {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.print-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
  border: none;
}

.btn-outline {
  background-color: white;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.loading, .error-message {
  text-align: center;
  padding: 2rem;
  font-size: 1rem;
}

/* Invoice Document Styles */
.invoice-document {
  background-color: white;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.company-info h1 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  color: #1e293b;
}

.company-info p {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  color: #64748b;
}

.invoice-title {
  text-align: right;
}

.invoice-title h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  color: #1e293b;
}

.invoice-number {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2563eb;
}

.invoice-status-banner {
  padding: 0.5rem;
  text-align: center;
  font-weight: 600;
  margin-bottom: 1.5rem;
  border-radius: 0.25rem;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-paid {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-cancelled {
  background-color: #fee2e2;
  color: #b91c1c;
}

.invoice-details-section {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.vendor-details, .invoice-details {
  width: 48%;
}

h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  color: #1e293b;
  font-weight: 600;
}

.details-box {
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  padding: 1rem;
  background-color: #f8fafc;
}

.vendor-name {
  font-weight: 600;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.details-table {
  width: 100%;
  border-collapse: collapse;
}

.details-table th {
  text-align: left;
  font-weight: 600;
  padding: 0.25rem 0;
  width: 40%;
  color: #475569;
}

.details-table td {
  padding: 0.25rem 0;
}

.invoice-items-section {
  margin-bottom: 1.5rem;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.items-table th {
  text-align: left;
  padding: 0.5rem;
  background-color: #f1f5f9;
  border-bottom: 1px solid #cbd5e1;
  font-weight: 600;
  color: #475569;
}

.items-table td {
  padding: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.items-table tfoot td {
  border-top: 2px solid #cbd5e1;
  font-weight: 600;
}

.text-right {
  text-align: right;
}

.related-receipts-section {
  margin-bottom: 1.5rem;
}

.receipt-detail {
  margin-bottom: 1rem;
}

.receipt-header {
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.receipt-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.receipt-table th {
  text-align: left;
  padding: 0.375rem;
  background-color: #f1f5f9;
  border-bottom: 1px solid #cbd5e1;
  font-weight: 600;
  color: #475569;
}

.receipt-table td {
  padding: 0.375rem;
  border-bottom: 1px solid #e2e8f0;
}

.payment-section, .notes-section {
  margin-bottom: 1.5rem;
}

.invoice-footer {
  margin-top: 2rem;
  display: flex;
  justify-content: space-between;
}

.footer-signature {
  width: 50%;
}

.signature-line {
  margin-bottom: 1.5rem;
}

.footer-copy {
  text-align: right;
  font-size: 0.75rem;
  color: #64748b;
}

/* Print specific styles */
  @media print {
    body {
      background-color: white;
    }

    .print-controls {
      display: none;
    }

    .invoice-print-container {
      padding: 0;
      font-size: 10pt;
      max-width: 180mm;
      margin-left: auto;
      margin-right: 0;
    }

    .invoice-document {
      border: none;
      box-shadow: none;
      padding: 0;
    }

    @page {
      size: A4;
      margin: 1cm;
    }
  }

.btn-danger {
  background-color: #dc2626;
  color: white;
  border: none;
}

.btn-danger:hover {
  background-color: #b91c1c;
}
</style>
