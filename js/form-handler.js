// Form submission handler for XAMPP testing
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Get button and show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            try {
                // Send AJAX request
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message in a nice popup
                    showMessagePopup('success', data.message);
                    
                    // Reset form
                    contactForm.reset();
                    
                    // Reset CAPTCHA if exists
                    if (typeof refreshCaptcha === 'function') {
                        refreshCaptcha();
                    }
                    
                    // Reset terms checkbox
                    const termsCheckbox = document.getElementById('termsCheckbox');
                    if (termsCheckbox) {
                        termsCheckbox.checked = false;
                    }
                    
                    // Show preview link
                    setTimeout(() => {
                        showPreviewLink();
                    }, 2000);
                    
                } else {
                    showMessagePopup('error', data.message);
                }
                
            } catch (error) {
                console.error('Error:', error);
                showMessagePopup('error', 'Connection error. Please check if XAMPP Apache is running.');
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    // Message popup function
    function showMessagePopup(type, message) {
        // Remove existing popup
        const existingPopup = document.getElementById('form-message-popup');
        if (existingPopup) existingPopup.remove();
        
        // Create popup
        const popup = document.createElement('div');
        popup.id = 'form-message-popup';
        popup.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        
        if (type === 'success') {
            popup.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
            popup.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <div>
                        <h3 style="margin: 0 0 10px 0;">Success!</h3>
                        <div>${message}</div>
                    </div>
                </div>
            `;
        } else {
            popup.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
            popup.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
                    <div>
                        <h3 style="margin: 0 0 10px 0;">Error</h3>
                        <div>${message}</div>
                    </div>
                </div>
            `;
        }
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
        `;
        closeBtn.onclick = () => popup.remove();
        popup.appendChild(closeBtn);
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(popup);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (popup.parentNode) {
                popup.style.animation = 'slideOut 0.3s ease';
                popup.style.transform = 'translateX(100%)';
                popup.style.opacity = '0';
                setTimeout(() => popup.remove(), 300);
            }
        }, 10000);
    }
    
    // Show preview link
    function showPreviewLink() {
        const previewDiv = document.createElement('div');
        previewDiv.id = 'preview-link';
        previewDiv.style.cssText = `
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        `;
        
        previewDiv.innerHTML = `
            <p><i class="fas fa-eye"></i> <strong>Local Test Preview Available</strong></p>
            <p style="font-size: 14px; color: #666;">
                View your submitted enquiry details in a formatted preview.
            </p>
            <a href="enquiry_preview.html" target="_blank" style="
                display: inline-block;
                background: #2196f3;
                color: white;
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 10px;
            ">
                <i class="fas fa-external-link-alt"></i> View Enquiry Preview
            </a>
        `;
        
        const form = document.querySelector('.contact-form');
        if (form) {
            form.appendChild(previewDiv);
        }
    }
});