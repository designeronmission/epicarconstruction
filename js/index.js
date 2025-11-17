        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide');
            const thumbnails = document.querySelectorAll('.thumbnail');
            const indicators = document.querySelectorAll('.indicator');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            let currentSlide = 0;
            let slideInterval;
            const slideDuration = 6000; // 6 seconds
            
            // Function to show a specific slide
            function showSlide(index, animateFromThumb = false) {
                // If we're animating from a thumbnail, create the animation effect
                if (animateFromThumb) {
                    const clickedThumb = thumbnails[index];
                    const thumbRect = clickedThumb.getBoundingClientRect();
                    const mainSlider = document.querySelector('.main-slider');
                    const mainRect = mainSlider.getBoundingClientRect();
                    
                    // Create a clone of the thumbnail for animation
                    const animatingThumb = clickedThumb.cloneNode(true);
                    animatingThumb.classList.add('thumbnail-animating');
                    animatingThumb.style.position = 'fixed';
                    animatingThumb.style.left = `${thumbRect.left}px`;
                    animatingThumb.style.top = `${thumbRect.top}px`;
                    animatingThumb.style.width = `${thumbRect.width}px`;
                    animatingThumb.style.height = `${thumbRect.height}px`;
                    animatingThumb.style.zIndex = '1000';
                    document.body.appendChild(animatingThumb);
                    
                    // Calculate final position and size
                    const targetWidth = mainRect.width;
                    const targetHeight = mainRect.height;
                    const targetLeft = mainRect.left;
                    const targetTop = mainRect.top;
                    
                    // Animate the thumbnail to full size
                    setTimeout(() => {
                        animatingThumb.classList.add('active');
                        animatingThumb.style.left = `${targetLeft}px`;
                        animatingThumb.style.top = `${targetTop}px`;
                        animatingThumb.style.width = `${targetWidth}px`;
                        animatingThumb.style.height = `${targetHeight}px`;
                    }, 10);
                    
                    // Remove the animation element and show the actual slide
                    setTimeout(() => {
                        // Remove active class from all slides, thumbnails and indicators
                        slides.forEach(slide => slide.classList.remove('active'));
                        thumbnails.forEach(thumb => thumb.classList.remove('active'));
                        indicators.forEach(indicator => indicator.classList.remove('active'));
                        
                        // Update current slide index
                        currentSlide = index;
                        
                        // Add active class to current slide, thumbnail and indicator
                        slides[currentSlide].classList.add('active');
                        thumbnails[currentSlide].classList.add('active');
                        indicators[currentSlide].classList.add('active');
                        
                        // Remove the animation element
                        document.body.removeChild(animatingThumb);
                    }, 500);
                } else {
                    // Standard slide transition without animation
                    // Remove active class from all slides, thumbnails and indicators
                    slides.forEach(slide => slide.classList.remove('active'));
                    thumbnails.forEach(thumb => thumb.classList.remove('active'));
                    indicators.forEach(indicator => indicator.classList.remove('active'));
                    
                    // Update current slide index
                    currentSlide = index;
                    
                    // Add active class to current slide, thumbnail and indicator
                    slides[currentSlide].classList.add('active');
                    thumbnails[currentSlide].classList.add('active');
                    indicators[currentSlide].classList.add('active');
                }
            }
            
            // Function to show next slide
            function nextSlide() {
                let nextIndex = (currentSlide + 1) % slides.length;
                showSlide(nextIndex);
            }
            
            // Function to show previous slide
            function prevSlide() {
                let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(prevIndex);
            }
            
            // Add click event to thumbnails
            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', () => {
                    resetInterval();
                    showSlide(index, true);
                });
            });
            
            // Add click event to indicators
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    resetInterval();
                    showSlide(index);
                });
            });
            
            // Add click event to navigation buttons
            prevBtn.addEventListener('click', () => {
                resetInterval();
                prevSlide();
            });
            
            nextBtn.addEventListener('click', () => {
                resetInterval();
                nextSlide();
            });
            
            // Auto slide function
            function startInterval() {
                slideInterval = setInterval(nextSlide, slideDuration);
            }
            
            // Reset interval when user interacts
            function resetInterval() {
                clearInterval(slideInterval);
                startInterval();
            }
            
            // Initialize the slider
            startInterval();
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    resetInterval();
                    prevSlide();
                } else if (e.key === 'ArrowRight') {
                    resetInterval();
                    nextSlide();
                }
            });
        });