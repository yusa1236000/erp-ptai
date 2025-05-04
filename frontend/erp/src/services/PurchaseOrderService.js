// src/services/PurchaseOrderService.js
import axios from 'axios';

class PurchaseOrderService {
  /**
   * Get all purchase orders with optional filtering
   * @param {Object} params - Query parameters for filtering, pagination, and sorting
   * @returns {Promise} - API response
   */
  getAllPurchaseOrders(params = {}) {
    return axios.get('/purchase-orders', { params });
  }

  /**
   * Get a single purchase order by ID
   * @param {Number} id - Purchase Order ID
   * @returns {Promise} - API response
   */
  getPurchaseOrderById(id) {
    return axios.get(`/purchase-orders/${id}`);
  }

  /**
   * Create a new purchase order
   * @param {Object} purchaseOrderData - Purchase order data
   * @returns {Promise} - API response
   */
  createPurchaseOrder(purchaseOrderData) {
    return axios.post('/purchase-orders', purchaseOrderData);
  }

  /**
   * Update an existing purchase order
   * @param {Number} id - Purchase Order ID
   * @param {Object} purchaseOrderData - Updated purchase order data
   * @returns {Promise} - API response
   */
  updatePurchaseOrder(id, purchaseOrderData) {
    return axios.put(`/purchase-orders/${id}`, purchaseOrderData);
  }

  /**
   * Delete a purchase order
   * @param {Number} id - Purchase Order ID
   * @returns {Promise} - API response
   */
  deletePurchaseOrder(id) {
    return axios.delete(`/purchase-orders/${id}`);
  }

  /**
   * Update the status of a purchase order
   * @param {Number} id - Purchase Order ID
   * @param {String} status - New status
   * @returns {Promise} - API response
   */
  updatePurchaseOrderStatus(id, status) {
    return axios.patch(`/purchase-orders/${id}/status`, { status });
  }

  /**
   * Create a purchase order from a vendor quotation
   * @param {Number} quotationId - Vendor Quotation ID
   * @returns {Promise} - API response
   */
  createFromQuotation(quotationId) {
    return axios.post('/purchase-orders/create-from-quotation', { quotation_id: quotationId });
  }

  /**
   * Get all vendors for purchase order creation/editing
   * @returns {Promise} - API response
   */
  getVendors() {
    return axios.get('/vendors', { params: { is_active: true } });
  }

  /**
   * Get all purchasable items for purchase order line items
   * @returns {Promise} - API response
   */
  getItems() {
    return axios.get('/items/purchasable');
  }

  /**
   * Get all units of measure for purchase order line items
   * @returns {Promise} - API response
   */
  getUnitsOfMeasure() {
    return axios.get('/uoms');
  }

  /**
   * Get vendor quotation by ID
   * @param {Number} id - Vendor Quotation ID
   * @returns {Promise} - API response
   */
  getVendorQuotationById(id) {
    return axios.get(`/vendor-quotations/${id}`);
  }

  /**
   * Convert purchase order currency
   * @param {Number} id - Purchase Order ID
   * @param {String} currencyCode - New currency code
   * @param {Boolean} useExchangeRateDate - Whether to use exchange rate from PO date or current date
   * @returns {Promise} - API response 
   */
  convertCurrency(id, currencyCode, useExchangeRateDate = true) {
    return axios.post(`/purchase-orders/${id}/convert-currency`, {
      currency_code: currencyCode,
      use_exchange_rate_date: useExchangeRateDate
    });
  }
}

export default new PurchaseOrderService();