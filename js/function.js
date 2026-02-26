(function ($) {
    "use strict";
    
    var $window = $(window); 
    var $body = $('body'); 

    /* Preloader Effect */
    $window.on('load', function(){
        $(".preloader").fadeOut(600);
    });

    /* Sticky Header */    
    if($('.active-sticky-header').length){
        $window.on('resize', function(){
            setHeaderHeight();
        });

        function setHeaderHeight(){
            $("header.main-header").css("height", $('header .header-sticky').outerHeight());
        }    
    
        $(window).on("scroll", function() {
            var fromTop = $(window).scrollTop();
            setHeaderHeight();
            var headerHeight = $('header .header-sticky').outerHeight()
            $("header .header-sticky").toggleClass("hide", (fromTop > headerHeight + 100));
            $("header .header-sticky").toggleClass("active", (fromTop > 600));
        });
    }
    
    
    /* Slick Menu JS */
    $('#menu').slicknav({
        label : '',
        prependTo : '.responsive-menu'
    });


    if($("a[href='#top']").length){
        $("a[href='#top']").click(function() {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return false;
        });
    }

    /* Hero Slider Layout JS */
    const hero_slider_layout = new Swiper('.hero-slider-layout .swiper', {
        slidesPerView : 1,
        speed: 1000,
        spaceBetween: 0,
        loop: true,
        autoplay: {
            delay: 4000,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });

    /* testimonial Slider JS */
    if ($('.testimonial-slider').length) {
        const testimonial_slider = new Swiper('.testimonial-slider .swiper', {
            slidesPerView : 1,
            speed: 1000,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 3000,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768:{
                    slidesPerView: 2,
                },
                991:{
                    slidesPerView: 3,
                }
            }
        });
    }

    /* Services Single Image Carousel JS */
    if ($('.service-images-slider').length) {
        const property_photos_carousel = new Swiper('.service-images-slider .swiper', {
            slidesPerView : 1,
            speed: 1000,
            spaceBetween: 10,
            loop: true,
            centeredSlides: true,
            autoplay: {
                delay: 5000,
            },
            navigation: {
                nextEl: '.swiper-arrow-next',
                prevEl: '.swiper-arrow-prev',
            }
        });
    }

    /* Init Counter */
    if ($('.counter').length) {
        $('.counter').counterUp({ delay: 6, time: 3000 });
    }

    /* Image Reveal Animation */
    if ($('.reveal').length) {
        gsap.registerPlugin(ScrollTrigger);
        let revealContainers = document.querySelectorAll(".reveal");
        revealContainers.forEach((container) => {
            let image = container.querySelector("img");
            let tl = gsap.timeline({
                scrollTrigger: {
                    trigger: container,
                    toggleActions: "play none none none"
                }
            });
            tl.set(container, {
                autoAlpha: 1
            });
            tl.from(container, 1, {
                xPercent: -100,
                ease: Power2.out
            });
            tl.from(image, 1, {
                xPercent: 100,
                scale: 1,
                delay: -1,
                ease: Power2.out
            });
        });
    }

    /* Text Effect Animation */
    if ($('.text-anime-style-1').length) {
        let staggerAmount     = 0.05,
            translateXValue = 0,
            delayValue         = 0.5,
           animatedTextElements = document.querySelectorAll('.text-anime-style-1');
        
        animatedTextElements.forEach((element) => {
            let animationSplitText = new SplitText(element, { type: "chars, words" });
                gsap.from(animationSplitText.words, {
                duration: 1,
                delay: delayValue,
                x: 20,
                autoAlpha: 0,
                stagger: staggerAmount,
                scrollTrigger: { trigger: element, start: "top 85%" },
                });
        });        
    }
    
    if ($('.text-anime-style-2').length) {                
        let     staggerAmount         = 0.05,
                 translateXValue    = 20,
                 delayValue         = 0.5,
                 easeType             = "power2.out",
                 animatedTextElements = document.querySelectorAll('.text-anime-style-2');
        
        animatedTextElements.forEach((element) => {
            let animationSplitText = new SplitText(element, { type: "chars, words" });
                gsap.from(animationSplitText.chars, {
                    duration: 1,
                    delay: delayValue,
                    x: translateXValue,
                    autoAlpha: 0,
                    stagger: staggerAmount,
                    ease: easeType,
                    scrollTrigger: { trigger: element, start: "top 85%"},
                });
        });        
    }
    
    if ($('.text-anime-style-3').length) {        
        let    animatedTextElements = document.querySelectorAll('.text-anime-style-3');
        
         animatedTextElements.forEach((element) => {
            //Reset if needed
            if (element.animation) {
                element.animation.progress(1).kill();
                element.split.revert();
            }

            element.split = new SplitText(element, {
                type: "lines,words,chars",
                linesClass: "split-line",
            });
            gsap.set(element, { perspective: 400 });

            gsap.set(element.split.chars, {
                opacity: 0,
                x: "50",
            });

            element.animation = gsap.to(element.split.chars, {
                scrollTrigger: { trigger: element,    start: "top 90%" },
                x: "0",
                y: "0",
                rotateX: "0",
                opacity: 1,
                duration: 1,
                ease: Back.easeOut,
                stagger: 0.02,
            });
        });        
    }

    /* Parallaxie js */
    var $parallaxie = $('.parallaxie');
    if($parallaxie.length && ($window.width() > 991))
    {
        if ($window.width() > 768) {
            $parallaxie.parallaxie({
                speed: 0.55,
                offset: 0,
            });
        }
    }

    /* Zoom Gallery screenshot */
    $('.project-gallery-items').magnificPopup({
        delegate: 'a',
        type: 'image',
        closeOnContentClick: false,
        closeBtnInside: false,
        mainClass: 'mfp-with-zoom',
        image: {
            verticalFit: true,
        },
        gallery: {
            enabled: true
        },
        zoom: {
            enabled: true,
            duration: 300, // don't foget to change the duration also in CSS
            opener: function(element) {
              return element.find('img');
            }
        }
    });

    /* Animated Wow Js */    
    new WOW().init();

    /* Popup Video */
    if ($('.popup-video').length) {
        $('.popup-video').magnificPopup({
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: true
        });
    }
    
    // ========== CONTACT FORM HANDLER ==========
    if ($('#contactForm').length) {
        console.log('✅ Contact form handler initialized');
        
        // Remove old form handler if exists
        $('#contactForm').off('submit');
        
        // CAPTCHA Setup
        let currentCaptcha = generateCaptcha();
        $('#captchaText').text(currentCaptcha);
        
        // CAPTCHA Refresh
        $('#refreshCaptcha').on('click', function(e) {
            e.preventDefault();
            currentCaptcha = generateCaptcha();
            $('#captchaText').text(currentCaptcha);
            $('#captchaInput').val('').css('border-color', '');
            $('#captchaStatus').text('').css('color', '');
        });
        
        // CAPTCHA Real-time Validation
        $('#captchaInput').on('input', function() {
            const inputValue = $(this).val().toUpperCase();
            const $status = $('#captchaStatus');
            
            if (inputValue === currentCaptcha) {
                $(this).css('border-color', '#27ae60');
                $status.text('✓ CAPTCHA verified').css('color', '#27ae60');
            } else if (inputValue.length >= currentCaptcha.length) {
                $(this).css('border-color', '#e74c3c');
                $status.text('✗ Invalid CAPTCHA').css('color', '#e74c3c');
            } else {
                $(this).css('border-color', '');
                $status.text('');
            }
        });
        
        // Phone number formatting
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 10) value = value.slice(0, 10);
            $(this).val(value);
        });
        
        // Form Submission Handler
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            console.log('📝 Form submission started');
            
            // Get button and save original text
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = $submitBtn.html();
            
            // ========== VALIDATION ==========
            let isValid = true;
            let errorMessage = '';
            
            // 1. Validate CAPTCHA
 const captchaInput = $('#captchaInput').val().toUpperCase().trim();
if (!captchaInput) {
    isValid = false;
    errorMessage = 'Please enter the CAPTCHA code.';
    $('#captchaInput').focus();
    console.log('❌ CAPTCHA is empty');
} else if (captchaInput !== currentCaptcha) {
    isValid = false;
    errorMessage = 'Please enter the correct CAPTCHA code.';
    $('#captchaInput').focus();
    console.log('❌ CAPTCHA validation failed', {input: captchaInput, expected: currentCaptcha});
}
            
            // 2. Validate Terms (only if CAPTCHA passed)
            if (isValid && !$('#termsCheckbox').is(':checked')) {
                isValid = false;
                errorMessage = 'Please agree to the Terms and Conditions and Privacy Policy.';
                $('#termsCheckbox').focus();
                console.log('❌ Terms validation failed');
            }
            
            // 3. Validate required fields
            if (isValid) {
                const requiredFields = ['name', 'email', 'phone', 'location', 'message'];
                for (const field of requiredFields) {
                    const $field = $(`[name="${field}"]`);
                    if (!$field.val().trim()) {
                        isValid = false;
                        errorMessage = `Please fill in the ${field.replace(/([A-Z])/g, ' $1').toLowerCase()} field.`;
                        $field.focus();
                        console.log(`❌ ${field} validation failed`);
                        break;
                    }
                }
            }
            
            // If validation failed, show error and stop
            if (!isValid) {
                showAlert('error', errorMessage);
                return false;
            }
            
            // ========== SUBMIT FORM ==========
            console.log('✅ All validations passed, submitting...');
            
            // Show loading state
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
            
            // Collect form data
            const formData = new FormData(this);
            
            // Add timestamp for debugging
            formData.append('submission_time', new Date().toISOString());
            
            // Send AJAX request
            $.ajax({
                url: 'send_email.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 30000,



              success: function(response) {
    console.log('✅ Server Response:', response);
    
    if (response.success) {
        // Enhanced success message with email status
        let successMsg = response.message;
        
        // Add visual email status
        if (response.emails_sent) {
            const adminStatus = response.emails_sent.admin ? 
                '<span style="color: #27ae60;">✓ Admin</span>' : 
                '<span style="color: #e74c3c;">✗ Admin</span>';
            
            const customerStatus = response.emails_sent.customer ? 
                '<span style="color: #27ae60;">✓ Customer</span>' : 
                '<span style="color: #e74c3c;">✗ Customer</span>';
            
            successMsg += '<br><br><small style="color: #666;">📧 Email Status: ' + 
                         adminStatus + ' | ' + customerStatus + '</small>';
        }
        
        // Show success message
        showAlert('success', successMsg);
        
        // Reset form
        $('#contactForm')[0].reset();
        
        // Refresh CAPTCHA
        currentCaptcha = generateCaptcha();
        $('#captchaText').text(currentCaptcha);
        $('#captchaInput').val('').css('border-color', '');
        $('#captchaStatus').text('').css('color', '');
        
        // Uncheck terms
        $('#termsCheckbox').prop('checked', false);
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 500);
        
        // Log success
        console.log('🎉 Form submitted successfully. Ref ID:', response.ref_id);
        console.log('📧 Email status:', response.emails_sent);
        
    } else {
        // Show error from server
        showAlert('error', response.message || 'Server returned an error.');
        console.error('❌ Server error:', response);
    }
},


                
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', { status: status, error: error, xhr: xhr });
                    
                    let userMessage = 'An error occurred while submitting the form. ';
                    
                    if (xhr.status === 404) {
                        userMessage += 'Server file not found. Please contact administrator.';
                    } else if (xhr.status === 500) {
                        userMessage += 'Server error. Please try again later.';
                    } else if (status === 'timeout') {
                        userMessage += 'Request timed out. Please check your connection.';
                    } else if (xhr.responseText) {
                        // Try to parse error response
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            userMessage = errorResponse.message || userMessage;
                        } catch (e) {
                            // Not JSON
                            userMessage += 'Please try again.';
                        }
                    }
                    
                    showAlert('error', userMessage);
                },
                
                complete: function() {
                    // Always restore button
                    $submitBtn.html(originalBtnText).prop('disabled', false);
                    console.log('🔄 Form submission process completed');
                }
            });
            
            return false;
        });



        
        // ========== HELPER FUNCTIONS ==========
        
        // Generate CAPTCHA
        function generateCaptcha() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let captcha = '';
            for (let i = 0; i < 5; i++) {
                captcha += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return captcha;
        }
        
        // Show alert message
        function showAlert(type, message) {
            // Remove existing alerts
            $('.form-alert').remove();
            
            // Alert styles
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const title = type === 'success' ? 'Success!' : 'Error!';
            
            // Create alert element
            const $alert = $(`
                <div class="form-alert alert ${alertClass} alert-dismissible fade show" role="alert" 
                     style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 500px; 
                            animation: slideIn 0.3s ease;">
                    <div class="d-flex align-items-start">
                        <i class="fas ${icon} me-3 mt-1" style="font-size: 24px;"></i>
                        <div>
                            <h6 class="alert-heading mb-2">${title}</h6>
                            <div class="mb-0">${message}</div>
                        </div>
                        <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" 
                                style="margin-top: -5px;"></button>
                    </div>
                </div>
            `);
            
            // Add slideIn animation
            if (!$('#alert-animation').length) {
                $('head').append(`
                    <style id="alert-animation">
                        @keyframes slideIn {
                            from { transform: translateX(100%); opacity: 0; }
                            to { transform: translateX(0); opacity: 1; }
                        }
                        @keyframes slideOut {
                            from { transform: translateX(0); opacity: 1; }
                            to { transform: translateX(100%); opacity: 0; }
                        }
                    </style>
                `);
            }
            
            // Add to page
            $('body').append($alert);
            
            // Close button functionality
            $alert.find('.btn-close').on('click', function() {
                $alert.css('animation', 'slideOut 0.3s ease');
                setTimeout(() => $alert.remove(), 300);
            });
            
            // Auto remove after 8 seconds for errors, 10 seconds for success
            const removeTime = type === 'success' ? 10000 : 8000;
            setTimeout(() => {
                if ($alert.length) {
                    $alert.css('animation', 'slideOut 0.3s ease');
                    setTimeout(() => $alert.remove(), 300);
                }
            }, removeTime);
        }
        
        // Real-time form validation
        $('#name, #email, #location, #message').on('blur', function() {
            const $field = $(this);
            if (!$field.val().trim()) {
                $field.css('border-color', '#e74c3c');
            } else {
                $field.css('border-color', '#27ae60');
            }
        });
        
        // Email validation
        $('#email').on('blur', function() {
            const email = $(this).val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email)) {
                $(this).css('border-color', '#e74c3c');
                showAlert('error', 'Please enter a valid email address.');
            }
        });
        
        console.log('🚀 Contact form handler ready');
    }
    
})(jQuery);