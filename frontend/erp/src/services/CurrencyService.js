// src/services/CurrencyService.js
import axios from "axios";

const API_URL = "";

export default {
    /**
     * Get all available currencies
     */
    getAllCurrencies() {
        return axios.get(`${API_URL}/currencies`);
    },

    /**
     * Get current exchange rates
     * 
     * @param {String} baseCurrency - Base currency code
     */
    getExchangeRates(baseCurrency = 'USD') {
        return axios.get(`${API_URL}/exchange-rates`, {
            params: { base_currency: baseCurrency }
        });
    },

    /**
     * Get historical exchange rates for a date
     * 
     * @param {String} date - Date in YYYY-MM-DD format
     * @param {String} baseCurrency - Base currency code
     */
    getHistoricalRates(date, baseCurrency = 'USD') {
        return axios.get(`${API_URL}/exchange-rates/historical`, {
            params: { 
                date: date,
                base_currency: baseCurrency 
            }
        });
    },

    /**
     * Convert amount between currencies
     * 
     * @param {Number} amount - Amount to convert
     * @param {String} fromCurrency - Source currency code
     * @param {String} toCurrency - Target currency code
     * @param {String} date - Optional date for historical conversion
     */
    convertAmount(amount, fromCurrency, toCurrency, date = null) {
        const params = {
            amount: amount,
            from_currency: fromCurrency,
            to_currency: toCurrency
        };
        
        if (date) {
            params.date = date;
        }
        
        return axios.post(`${API_URL}/convert-currency`, params);
    }
};