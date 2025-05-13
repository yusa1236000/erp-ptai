<!-- src/views/sales/SalesInvoicePrint.vue -->
<template>
  <div class="print-container">
    <!-- Action Buttons (Hide when printing) -->
    <div class="print-actions" v-if="!isPrinting">
      <button class="btn btn-primary" @click="printInvoice">
        <i class="fas fa-print"></i> Print
      </button>
      <button class="btn btn-secondary" @click="goBack">
        <i class="fas fa-arrow-left"></i> Back
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Loading invoice...
    </div>

    <!-- Empty/Error state -->
    <div v-else-if="!invoice" class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <h3>Invoice not found</h3>
      <p>
        The invoice you're looking for doesn't exist or may have been
        deleted.
      </p>
      <button class="btn btn-primary" @click="goBack">
        Back to invoices list
      </button>
    </div>

    <!-- Invoice content -->
    <div v-else class="invoice-document">
      <!-- Company Header -->
      <div class="company-header">
        <div class="company-info">
          <div class="company-logo" v-if="logoUrl">
            <img :src="logoUrl" alt="Company Logo" />
          </div>
          <div class="company-name">{{ companyName }}</div>
          <div class="company-address">
            {{ companyAddress1 }}<br>
            {{ companyAddress2 }}
          </div>
        </div>
        <div class="invoice-info">
          <div class="invoice-title">INVOICE</div>
          <div class="invoice-number">No. Inv.: {{ invoice.invoice_number }}</div>
          <div class="invoice-date">Date. Inv.: {{ formatDate(invoice.invoice_date) }}</div>
          <div class="invoice-page">Page: 1 of 1</div>
        </div>
      </div>

      <!-- Customer Info -->
      <div class="customer-section">
        <div class="sold-to">
          <div class="label">SOLD TO:</div>
          <div class="customer-box">
            {{ invoice.customer?.name }}<br>
            {{ invoice.customer?.address || 'No address' }}<br>
            {{ invoice.customer?.city || '' }} {{ invoice.customer?.postal_code || '' }}<br>
            {{ invoice.customer?.country || '' }}
          </div>
        </div>
        <div class="ship-to">
          <div class="label">GOOD DELIVERY TO:</div>
          <div class="customer-box">
            {{ invoice.customer?.shipping_name || invoice.customer?.name }}<br>
            {{ invoice.customer?.shipping_address || invoice.customer?.address || 'No address' }}<br>
            {{ invoice.customer?.shipping_city || invoice.customer?.city || '' }} {{ invoice.customer?.shipping_postal_code || invoice.customer?.postal_code || '' }}<br>
            {{ invoice.customer?.shipping_country || invoice.customer?.country || '' }}
          </div>
        </div>
      </div>

      <!-- Order Info -->
      <div class="order-info">
        <div class="order-info-item">
          <div class="order-info-label">PO No.</div>
          <div class="order-info-value">{{ invoice.reference || '-' }}</div>
        </div>
        <div class="order-info-item">
          <div class="order-info-label">PO Date</div>
          <div class="order-info-value">{{ invoice.po_date ? formatDate(invoice.po_date) : '' }}</div>
        </div>
        <div class="order-info-item">
          <div class="order-info-label">SALES PERSON</div>
          <div class="order-info-value">{{ salesPerson }}</div>
        </div>
        <div class="order-info-item">
          <div class="order-info-label">SHIP VIA</div>
          <div class="order-info-value">{{ invoice.delivery?.shipping_method || '' }}</div>
        </div>
        <div class="order-info-item">
          <div class="order-info-label">TERMS</div>
          <div class="order-info-value">{{ invoice.payment_terms || 'Net 30 days' }}</div>
        </div>
        <div class="order-info-item">
          <div class="order-info-label">DO No.</div>
          <div class="order-info-value">{{ invoice.delivery?.delivery_number || '-' }}</div>
        </div>
      </div>

      <div class="order-details" v-if="orderNumber">
        <div class="order-number">CO-NUM: {{ orderNumber }}</div>
      </div>

      <!-- Invoice Items -->
      <table class="items-table">
        <thead>
          <tr>
            <th class="part-number">PART NUMBER</th>
            <th class="do-number">DO REMARKS</th>
            <th class="qty">Qty SHIPPED</th>
            <th class="uom">UOM</th>
            <th class="description">Description</th>
            <th class="unit-price">UNIT PRICE</th>
            <th class="amount">AMOUNT</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(line, index) in invoice.salesInvoiceLines" :key="line.line_id || `line-${index}`">
            <td class="part-number">{{ line.item?.item_code || 'N/A' }}</td>
            <td class="do-number">{{ invoice.delivery?.delivery_number || '-' }}</td>
            <td class="qty">{{ formatNumber(line.quantity) }}</td>
            <td class="uom">{{ getUomSymbol(line.uom_id) }}</td>
            <td class="description">{{ line.item?.name || 'Unknown Item' }}</td>
            <td class="unit-price">{{ formatCurrency(line.unit_price, false) }}</td>
            <td class="amount">{{ formatCurrency(line.total, false) }}</td>
          </tr>

          <!-- Empty rows for spacing/future items -->
          <tr v-for="i in getEmptyRows()" :key="`empty-${i}`">
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>

      <!-- Totals -->
      <div class="totals">
        <div class="totals-row">
          <div class="totals-label">Harga jual:</div>
          <div class="totals-value">{{ formatCurrency(calculateSubtotal(), true) }}</div>
        </div>
        <div class="totals-row">
          <div class="totals-label">Tax:</div>
          <div class="totals-value">
            {{ formatCurrency(invoice.tax_amount || 0, true) }}
            {{ taxRate > 0 ? `(${taxRate}%)` : '' }}
          </div>
        </div>
        <div class="totals-row">
          <div class="totals-label">TOTAL:</div>
          <div class="totals-value">{{ formatCurrency(invoice.total_amount, true) }}</div>
        </div>
      </div>

      <!-- Terms -->
      <div class="terms">
        <div class="terms-heading">Terms of Payment:</div>
        <ol class="terms-list">
          <li v-for="(term, index) in paymentTerms" :key="`term-${index}`" v-html="term"></li>
        </ol>
      </div>

      <!-- Footer with signatures -->
      <div class="invoice-footer">
        <div class="signature-box">
          <div>{{ companyName }}</div>
          <div class="signature-line"></div>
          <div>{{ signatoryName }}</div>
          <div>{{ companyCity }}</div>
        </div>
        <div class="signature-box">
          <div>{{ invoice.customer?.name }}</div>
          <div class="signature-line"></div>
          <div>Authorized Signature</div>
        </div>
      </div>

      <!-- Page info -->
      <div class="page-info">Page: 1 of 1</div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
  name: "SalesInvoicePrint",
  props: {
    id: {
      type: [Number, String],
      required: true
    }
  },
  setup(props) {
    const router = useRouter();
    //const route = useRoute();

    // Data
    const invoice = ref(null);
    const unitOfMeasures = ref([]);
    const isLoading = ref(true);
    const isPrinting = ref(false);
    const taxRate = ref(11); // Default tax rate in percent (adjust as needed)
    const salesPerson = ref("DELLA");
    const logoUrl = ref("/images/company-logo.png");
    const orderNumber = ref("SO3-24-010837");

    // Company information
    const companyName = ref("PT. ARMSTRONG INDUSTRI INDONESIA");
    const companyAddress1 = ref("EJIP INDUSTRIAL PARK PLOT 1 A-3 LIPPO CIKARANG");
    const companyAddress2 = ref("SUKARESMI, CIKARANG SELATAN, BEKASI 17857");
    const companyCity = ref("Jakarta Selatan (12940)");
    const signatoryName = ref("Welly Wibisono");

    // Payment terms
    const paymentTerms = ref([
      "Payment through bank is treated as settled only after clearing",
      "Interest for late payment rate 5% per month",
      "Bank DBS Indonesia<br>DBS Tower, 35th floor Ciputra Word I Jakarta,<br>Jl. Prof Dr. Satrio Kav. 3-5 Karet Kuningan<br>Account No.: 030-16069-48 (IDR)<br>Account No.: 030-16068-47 (USD)"
    ]);

    // Load invoice and reference data
    const loadData = async () => {
      isLoading.value = true;

      try {
        // Load unit of measures for reference
        const uomResponse = await axios.get("/unit-of-measures");
        unitOfMeasures.value = uomResponse.data || [];

        // Load invoice details
        const invoiceResponse = await axios.get(`/invoices/${props.id}`);
        invoice.value = invoiceResponse.data.data;

        // Set order number if available
        if (invoice.value?.sales_order) {
          orderNumber.value = invoice.value.sales_order.so_number;
        } else if (invoice.value?.delivery?.sales_order) {
          orderNumber.value = invoice.value.delivery.sales_order.so_number;
        }
      } catch (error) {
        console.error("Error loading invoice:", error);
        invoice.value = null;
      } finally {
        isLoading.value = false;
      }
    };

    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return "-";
      const date = new Date(dateString);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    };

    // Format number
    const formatNumber = (value) => {
      return new Intl.NumberFormat("id-ID").format(value || 0);
    };

    // Format currency
    const formatCurrency = (value, showCurrency = true) => {
      const formatted = new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 5,
        maximumFractionDigits: 5,
      }).format(value || 0);

      if (showCurrency && invoice.value) {
        return `${invoice.value.currency_code || 'USD'} ${formatted}`;
      }
      return formatted;
    };

    // Get UOM symbol
    const getUomSymbol = (uomId) => {
      const uom = unitOfMeasures.value.find((u) => u.uom_id === uomId);
      return uom ? uom.symbol : "UNIT";
    };

    // Calculate subtotal of all lines
    const calculateSubtotal = () => {
      if (!invoice.value || !invoice.value.salesInvoiceLines) return 0;
      return invoice.value.salesInvoiceLines.reduce(
        (sum, line) => sum + (line.subtotal || 0),
        0
      );
    };

    // Calculate total tax of all lines
    const calculateTotalTax = () => {
      if (!invoice.value || !invoice.value.salesInvoiceLines) return 0;
      return invoice.value.salesInvoiceLines.reduce(
        (sum, line) => sum + (line.tax || 0),
        0
      );
    };

    // Get empty rows to fill table
    const getEmptyRows = () => {
      const minRows = 3; // Minimum number of rows in the table
      const currentRows = invoice.value?.salesInvoiceLines?.length || 0;
      return Math.max(0, minRows - currentRows);
    };

    // Navigation methods
    const goBack = () => {
      router.push(`/sales/invoices/${props.id}`);
    };

    // Print the invoice
    const printInvoice = () => {
      isPrinting.value = true;
      setTimeout(() => {
        window.print();
        isPrinting.value = false;
      }, 300);
    };

    // Handle print event
    const handlePrintEvent = () => {
      isPrinting.value = true;
      setTimeout(() => {
        isPrinting.value = false;
      }, 300);
    };

    onMounted(() => {
      loadData();
      window.addEventListener("beforeprint", handlePrintEvent);
      window.addEventListener("afterprint", () => {
        isPrinting.value = false;
      });
    });

    onBeforeUnmount(() => {
      window.removeEventListener("beforeprint", handlePrintEvent);
      window.removeEventListener("afterprint", () => {
        isPrinting.value = false;
      });
    });

    return {
      invoice,
      isLoading,
      isPrinting,
      companyName,
      companyAddress1,
      companyAddress2,
      companyCity,
      signatoryName,
      logoUrl,
      salesPerson,
      orderNumber,
      taxRate,
      paymentTerms,
      formatDate,
      formatNumber,
      formatCurrency,
      getUomSymbol,
      calculateSubtotal,
      calculateTotalTax,
      getEmptyRows,
      goBack,
      printInvoice,
    };
  },
};
</script>

<style>
/* General styles */
.print-container {
  max-width: 210mm; /* A4 width */
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
  color: #333;
}

.print-actions {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-secondary {
  background-color: var(--gray-200);
  color: var(--gray-800);
}

.loading-indicator {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 50px 0;
  color: var(--gray-500);
}

.loading-indicator i {
  margin-right: 10px;
}

.empty-state {
  text-align: center;
  padding: 50px 0;
}

.empty-icon {
  font-size: 48px;
  color: var(--gray-300);
  margin-bottom: 20px;
}

/* Invoice Document Styles */
.invoice-document {
  background-color: white;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-size: 10pt;
  line-height: 1.4;
}

/* Company Header */
.company-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  border-bottom: 1px solid #000;
  padding-bottom: 10px;
}

.company-logo {
  margin-bottom: 10px;
}

.company-logo img {
  max-height: 50px;
  max-width: 150px;
}

.company-name {
  font-size: 16pt;
  font-weight: bold;
}

.company-address {
  font-size: 9pt;
  max-width: 60%;
}

.invoice-info {
  text-align: right;
}

.invoice-title {
  font-size: 14pt;
  font-weight: bold;
}

.invoice-number, .invoice-date, .invoice-page {
  font-size: 9pt;
}

/* Customer Info */
.customer-section {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.ship-to, .sold-to {
  width: 48%;
}

.label {
  font-weight: bold;
  text-transform: uppercase;
  margin-bottom: 5px;
}

.customer-box {
  border: 1px solid #000;
  padding: 5px;
  min-height: 100px;
}

/* Order Info */
.order-info {
  display: flex;
  margin-bottom: 10px;
  border: 1px solid #000;
  border-collapse: collapse;
}

.order-info-item {
  flex: 1;
  border-right: 1px solid #000;
  padding: 5px;
  text-align: center;
}

.order-info-item:last-child {
  border-right: none;
}

.order-info-label {
  font-weight: bold;
  font-size: 8pt;
}

.order-info-value {
  font-size: 9pt;
}

.order-details {
  margin-bottom: 10px;
}

/* Invoice Items */
.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.items-table th, .items-table td {
  border: 1px solid #000;
  padding: 5px;
}

.items-table th {
  background-color: #f2f2f2;
  font-weight: bold;
  text-align: center;
}

.items-table .part-number {
  width: 15%;
}

.items-table .do-number {
  width: 15%;
}

.items-table .description {
  width: 30%;
}

.items-table .qty {
  width: 10%;
  text-align: center;
}

.items-table .uom {
  width: 10%;
  text-align: center;
}

.items-table .unit-price {
  width: 10%;
  text-align: right;
}

.items-table .amount {
  width: 10%;
  text-align: right;
}

/* Totals */
.totals {
  width: 300px;
  margin-left: auto;
  margin-bottom: 20px;
}

.totals-row {
  display: flex;
  justify-content: space-between;
  padding: 5px 0;
}

.totals-label {
  font-weight: bold;
}

.totals-value {
  text-align: right;
  width: 150px;
}

/* Terms */
.terms {
  margin-bottom: 20px;
}

.terms-heading {
  font-weight: bold;
  margin-bottom: 5px;
}

.terms-list {
  padding-left: 20px;
}

/* Footer */
.invoice-footer {
  margin-top: 40px;
  display: flex;
  justify-content: space-between;
}

.signature-box {
  width: 45%;
  text-align: center;
}

.signature-line {
  border-top: 1px solid #000;
  margin-top: 60px;
  display: inline-block;
  width: 80%;
}

/* Page info */
.page-info {
  text-align: right;
  margin-top: 20px;
  font-size: 8pt;
}

/* Print specific styles */
@media print {
  body {
    margin: 0;
    padding: 0;
    background-color: white;
  }

  .print-container {
    padding: 0;
    max-width: none;
  }

  .print-actions {
    display: none !important;
  }

  .invoice-document {
    box-shadow: none;
    padding: 10mm;
    width: 190mm;
    margin: 0 auto;
  }

  @page {
    size: A4;
    margin: 0;
  }

  .signature-section,
  .terms,
  .totals {
    page-break-inside: avoid;
  }

  .items-table th {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    background-color: #f2f2f2 !important;
  }
}

@media (max-width: 768px) {
  .customer-section,
  .order-info {
    flex-direction: column;
  }

  .ship-to,
  .sold-to,
  .order-info-item {
    width: 100%;
    margin-bottom: 10px;
  }

  .order-info-item {
    border-right: none;
    border-bottom: 1px solid #000;
  }

  .order-info-item:last-child {
    border-bottom: none;
  }
}
</style>
