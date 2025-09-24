/**
 * Frontend Scripts for Donations & Custom Payment Plugin
 * 
 * @package Donations_Custom_Payment
 * @version 6.2.1
 * @author Mostafa Sadeghi
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('donations-custom-payment');
    const amountInput = document.getElementById('donations_custom_payment_amount');
    const submitBtn = document.getElementById('cpf-submit-btn');
    const presetBtns = document.querySelectorAll('.cpf-preset-btn');
    
    if (!form || !amountInput || !submitBtn) {
        console.log('Payment form not found on this page');
        return;
    }
    
    const btnText = submitBtn.querySelector('.cpf-btn-text');
    const loading = submitBtn.querySelector('.cpf-loading');
    
    /**
     * Format number with commas
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    /**
     * Convert Persian numbers to English
     * @param {string} str - String with Persian numbers
     * @returns {string} String with English numbers
     */
    function convertPersianToEnglish(str) {
        const persianNumbers = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        const englishNumbers = ['0','1','2','3','4','5','6','7','8','9'];
        for(let i = 0; i < persianNumbers.length; i++) {
            str = str.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
        }
        return str;
    }
    
    // Read amount from URL and set in field
    const url = window.location.href;
    const urlParts = url.split('?');
    
    if (urlParts.length > 1) {
        const amountFromUrl = urlParts[1];
        const numericAmount = parseInt(amountFromUrl);
        if (!isNaN(numericAmount) && numericAmount > 0) {
            amountInput.value = formatNumber(numericAmount);
        }
    }
    
    // Handle preset amount button clicks
    presetBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            presetBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const amount = this.getAttribute('data-amount');
            amountInput.value = formatNumber(amount);
        });
    });
    
    // Format amount input
    amountInput.addEventListener('input', function() {
        let value = convertPersianToEnglish(this.value);
        value = value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = formatNumber(value);
        }
        
        // Remove active from preset buttons
        presetBtns.forEach(b => b.classList.remove('active'));
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        const rawValue = amountInput.value.replace(/,/g, '');
        const numValue = parseInt(rawValue);
        
        // Get limits from DOM
        const minAmount = parseInt(amountInput.getAttribute('min')) || 1000;
        const maxAmount = parseInt(amountInput.getAttribute('max')) || 10000000;
        
        if (isNaN(numValue) || numValue < minAmount || numValue > maxAmount) {
            e.preventDefault();
            var message = cpfStrings.invalidAmount
                .replace('%1$s', formatNumber(minAmount))
                .replace('%2$s', formatNumber(maxAmount))
                .replace('%3$s', cpfStrings.currencyName);
            alert(message);
            return;
        }
        
        // Set clean numeric value for submission
        amountInput.value = rawValue;
        
        // Show loading state
        setTimeout(function() {
            if (submitBtn && btnText && loading) {
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                loading.style.display = 'block';
            }
        }, 100);
    });
});
