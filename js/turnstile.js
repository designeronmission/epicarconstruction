// Cloudflare Turnstile Integration
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cloudflare Turnstile initializing...');
    
    // Load Cloudflare Turnstile script
    loadTurnstileScript();
    
    // Initialize form handling
    initContactForm();
});

function loadTurnstileScript() {
    // Check if already loaded
    if (document.querySelector('script[src*="challenges.cloudflare.com"]')) {
        console.log('Turnstile script already loaded');
        return;
    }
    
    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    script.async = true;
    script.defer = true;
    script.onload = function() {
        console.log('Cloudflare Turnstile loaded successfully');
        hideLoading();
    };
    script.onerror = function() {
        console.error('Failed to load Cloudflare Turnstile');
        showTurnstileError('Failed to load security widget. Please refresh the page.');
    };
    
    document.head.appendChild(script);
}

function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (!contactForm) return;
    
    console.log('Initializing contact form...');
    
    // Form submission handler
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        
        // Validate form
        if (!validateForm()) {
            return false;
        }
        
        // Submit form
        submitForm();
    });
    
    // Form reset handler
    contactForm.addEventListener('reset', function() {
        console.log('Form reset');
        resetTurnstile();
    });
}

function validateForm() {
    console.log('Validating form...');
    
    const turnstileToken = document.getElementById('cf-turnstile-response').value;
    const termsCheckbox = document.getElementById('termsCheckbox');
    const errorElement = document.getElementById('turnstileError');
    const termsError = document.getElementById('termsError');
    
    // Reset errors
    errorElement.style.display = 'none';
    if (termsError) termsError.style.display = 'none';
    
    let isValid = true;
    
    // Check Turnstile
    if (!turnstileToken || turnstileToken.trim() === '') {
        errorElement.textContent = 'Please complete the security verification.';
        errorElement.style.display = 'block';
        isValid = false;
        
        // Scroll to widget
        const widget = document.querySelector('.turnstile-container');
        if (widget) {
            widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    // Check terms
    if (termsCheckbox && !termsCheckbox.checked) {
        if (termsError) {
            termsError.style.display = 'block';
        }
        isValid = false;
    }
    
    // Basic form validation
    const requiredFields = ['name', 'email', 'phone', 'location', 'message'];
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
            showFormStatus('Please fill in all required fields.', 'error');
            isValid = false;
        }
    });
    
    console.log('Form validation result:', isValid);
    return isValid;
}

function submitForm() {
    console.log('Submitting form...');
    
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    showFormStatus('Submitting your enquiry...', 'loading');
    
    // Submit via AJAX
    fetch('process-contact.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response received:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Success
            showFormStatus(data.message, 'success');
            showSuccessModal(data);
            form.reset();
            resetTurnstile();
        } else {
            // Error
            showFormStatus(data.message || 'Submission failed. Please try again.', 'error');
            
            // Show specific Turnstile error if present
            if (data.message && data.message.toLowerCase().includes('security')) {
                showTurnstileError(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showFormStatus('Network error. Please check your connection and try again.', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
        
        // Hide status after 10 seconds
        setTimeout(() => {
            hideFormStatus();
        }, 10000);
    });
}

// Cloudflare Turnstile Callbacks
window.onTurnstileSuccess = function(token) {
    console.log('Turnstile successful, token received');
    document.getElementById('cf-turnstile-response').value = token;
    document.getElementById('turnstileError').style.display = 'none';
    hideLoading();
};

window.onTurnstileError = function() {
    console.error('Turnstile error occurred');
    showTurnstileError('Verification failed. Please try again.');
    document.getElementById('cf-turnstile-response').value = '';
};

window.onTurnstileExpired = function() {
    console.log('Turnstile token expired');
    showTurnstileError('Verification expired. Please complete it again.');
    document.getElementById('cf-turnstile-response').value = '';
    resetTurnstile();
};

// Utility Functions
function resetTurnstile() {
    if (window.turnstile) {
        turnstile.reset();
        console.log('Turnstile reset');
    }
    document.getElementById('cf-turnstile-response').value = '';
    document.getElementById('turnstileError').style.display = 'none';
}

function hideLoading() {
    const loadingElement = document.getElementById('turnstileLoading');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
}

function showTurnstileError(message) {
    const errorElement = document.getElementById('turnstileError');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function showFormStatus(message, type = 'info') {
    const statusElement = document.getElementById('formStatus');
    if (!statusElement) return;
    
    statusElement.textContent = message;
    statusElement.className = type + '-status';
    statusElement.style.display = 'block';
}

function hideFormStatus() {
    const statusElement = document.getElementById('formStatus');
    if (statusElement) {
        statusElement.style.display = 'none';
    }
}

function showSuccessModal(data) {
    // Create modal HTML
    const modalHTML = `
        <div class="success-modal-overlay">
            <div class="success-modal">
                <div class="modal-icon">✓</div>
                <h3>Thank You!</h3>
                <p>${data.message}</p>
                <p><strong>Reference ID:</strong> ${data.ref_id || 'N/A'}</p>
                <p>We will contact you within 24 hours.</p>
                <button onclick="closeSuccessModal()" class="modal-close-btn">Close</button>
            </div>
        </div>
    `;
    
    // Add to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Add styles
    addModalStyles();
}

function closeSuccessModal() {
    const modal = document.querySelector('.success-modal-overlay');
    if (modal) {
        modal.remove();
    }
}

function addModalStyles() {
    const styleId = 'success-modal-styles';
    if (document.getElementById(styleId)) return;
    
    const styles = `
        .success-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        
        .success-modal {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            animation: slideUp 0.3s ease;
        }
        
        .modal-icon {
            font-size: 60px;
            color: #10b981;
            margin-bottom: 20px;
        }
        
        .success-modal h3 {
            color: #12223B;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .success-modal p {
            margin-bottom: 10px;
            color: #4b5563;
        }
        
        .modal-close-btn {
            background: #12223B;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            margin-top: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .modal-close-btn:hover {
            background: #1e293b;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    
    const styleTag = document.createElement('style');
    styleTag.id = styleId;
    styleTag.textContent = styles;
    document.head.appendChild(styleTag);
}



// Fallback if Turnstile doesn't load
setTimeout(function() {
    if (!window.turnstile) {
        console.warn('Turnstile not loaded, using fallback');
        showTurnstileError('Security widget failed to load. Please refresh or contact support.');
        
        // Optional: Allow form submission without Turnstile for testing
        // document.getElementById('cf-turnstile-response').value = 'fallback_token';
    }
}, 5000);