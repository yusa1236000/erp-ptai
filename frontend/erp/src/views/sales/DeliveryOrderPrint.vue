<!-- DeliveryOrder.vue -->
<template>
    <div class="delivery-order">
      <div class="header">
        <div class="company-info">
          <p>PT. ARMSTRONG INDUSTRI INDONESIA</p>
          <p>EJIP INDUSTRIAL PARK PLOT A1-3</p>
          <p>CIKARANG SELATAN, BEKASI -JAWA BARAT</p>
        </div>
        <div class="title">
          <h1>DELIVERY ORDER</h1>
        </div>
        <div class="do-info">
          <p>DO No : <span>NCVA24-0045</span></p>
          <p>DO Date : <span>28/11/2024</span></p>
          <p>Page : <span>1 of 1</span></p>
        </div>
      </div>

      <div class="addresses">
        <div class="address sold-to">
          <h3>SOLD TO :</h3>
          <div class="address-content">
            <p>PT. ZEPHYR INDONESIA</p>
            <p>JL. FLORES I BLOK C1 NO. 11</p>
            <p>KWS INDUSTRI MM2100</p>
            <p>MEKAR WANGI CIKARANG BARAT</p>
            <p>BEKASI-INDONESIA</p>
          </div>
        </div>
        <div class="address ship-to">
          <h3>SHIP TO :</h3>
          <div class="address-content">
            <p>PT. ZEPHYR INDONESIA</p>
            <p>JL. FLORES I BLOK C1 NO. 11</p>
            <p>KWS INDUSTRI MM2100</p>
            <p>MEKAR WANGI CIKARANG BARAT</p>
            <p>BEKASI-INDONESIA</p>
          </div>
        </div>
      </div>

      <div class="order-details">
        <table class="detail-table">
          <tr>
            <td>PO No.</td>
            <td>PO Date</td>
            <td>SALES PERSON</td>
            <td>SHIP VIA</td>
            <td>Inv No.</td>
            <td>Inv Date</td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td>KHUSNUL</td>
            <td>BY ROAD</td>
            <td>NCVA24-0522</td>
            <td></td>
          </tr>
        </table>
      </div>

      <div class="bc-info">
        <p>BC No. :</p>
      </div>

      <div class="items-list">
        <table class="items-table">
          <thead>
            <tr>
              <th>NO.</th>
              <th>Description</th>
              <th>Qty</th>
              <th>Pack</th>
              <th>UOM</th>
              <th>SO NO.</th>
              <th>L</th>
              <th>PART NO.</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>SF-1921239-01</td>
              <td>12</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SCALE FRONT, MM</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>2</td>
              <td>SF-1921240-01</td>
              <td>18</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SCALE REAR, MM</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>3</td>
              <td>SF-1921241-01</td>
              <td>12</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SCALE FRONT, INCH</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>4</td>
              <td>SF-1921242-01</td>
              <td>12</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SCALE REAR, INCH</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>5</td>
              <td>SF-1924578-00</td>
              <td>214</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SHEET,PANEL</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>6</td>
              <td>SF-1930522-00</td>
              <td>92</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SHEET,PANEL;B</td>
              <td>RETURN NG</td>
            </tr>
            <tr>
              <td>7</td>
              <td>SF-5638490-6</td>
              <td>70</td>
              <td></td>
              <td>PCS</td>
              <td></td>
              <td></td>
              <td>SCALE PANEL (F)</td>
              <td>RETURN NG</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="signatures">
        <div class="signature-section received">
          <p>Received the Abovementioned in Good Condition</p>
          <div class="signature-line">
            <p>Received BY</p>
          </div>
        </div>
        <div class="signature-section delivered">
          <p>Delivered By PT. ARMSTRONG INDUSTRI INDONESIA</p>
          <div class="signature-line">
            <p>Rec-#: 12/02-99/PLN/01</p>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'DeliveryOrder',
    props: {
      deliveryData: {
        type: Object,
        default: () => ({})
      }
    }
  }
  </script>

  <style scoped>
  .delivery-order {
    width: 210mm; /* A4 width */
    min-height: 297mm; /* A4 height */
    padding: 10mm;
    margin: 0 auto;
    font-family: Arial, sans-serif;
    font-size: 11px;
    color: #000;
    background-color: #fff;
    box-sizing: border-box;
    position: relative;
  }

  .header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    border-bottom: 1px solid #000;
    padding-bottom: 10px;
  }

  .company-info {
    flex: 1;
    text-align: left;
  }

  .company-info p {
    margin: 0;
    line-height: 1.4;
  }

  .title {
    flex: 1;
    text-align: center;
  }

  .title h1 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
  }

  .do-info {
    flex: 1;
    text-align: right;
  }

  .do-info p {
    margin: 0;
    line-height: 1.4;
  }

  .addresses {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
  }

  .address {
    width: 48%;
    border: 1px solid #000;
    padding: 5px;
  }

  .address h3 {
    margin: 0 0 5px 0;
    font-size: 12px;
    font-weight: bold;
  }

  .address-content p {
    margin: 0;
    line-height: 1.4;
  }

  .order-details {
    margin-bottom: 10px;
  }

  .detail-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
  }

  .detail-table td {
    border: 1px solid #000;
    padding: 5px;
    text-align: center;
  }

  .bc-info {
    margin: 10px 0;
  }

  .bc-info p {
    margin: 0;
  }

  .items-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
  }

  .items-table th,
  .items-table td {
    border: 1px solid #000;
    padding: 5px;
    text-align: center;
  }

  .items-table th {
    font-weight: bold;
    background-color: #f0f0f0;
  }

  .signatures {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
  }

  .signature-section {
    width: 48%;
    text-align: center;
  }

  .signature-line {
    margin-top: 70px; /* Space for signature */
    border-top: 1px solid #000;
    padding-top: 5px;
  }

  @media print {
    .delivery-order {
      width: 100%;
      height: 100%;
      padding: 0;
      margin: 0;
    }
  }
  </style>
