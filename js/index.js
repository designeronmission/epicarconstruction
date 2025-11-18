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



   // Mega Menu functionality
        document.addEventListener('DOMContentLoaded', function () {
            const servicesTrigger = document.getElementById('servicesTrigger');
            const servicesNavItem = document.getElementById('servicesNavItem');
            const megaMenu = document.getElementById('megaMenu');

            let hoverTimer;
            let isMenuOpen = false;

            // Open mega menu
            function openMegaMenu() {
                clearTimeout(hoverTimer);
                megaMenu.classList.add('active');
                isMenuOpen = true;
            }

            // Close mega menu
            function closeMegaMenuFunc() {
                megaMenu.classList.remove('active');
                isMenuOpen = false;
            }

            // Open on hover with delay
            servicesNavItem.addEventListener('mouseenter', function () {
                if (!isMenuOpen) {
                    hoverTimer = setTimeout(openMegaMenu, 300); // 300ms delay
                }
            });

            // Close on mouse leave with delay
            servicesNavItem.addEventListener('mouseleave', function (e) {
                // Check if mouse is leaving to outside the menu
                if (!megaMenu.contains(e.relatedTarget)) {
                    clearTimeout(hoverTimer);
                    if (isMenuOpen) {
                        hoverTimer = setTimeout(closeMegaMenuFunc, 500); // 500ms delay before closing
                    }
                }
            });

            // Keep menu open when hovering over it
            megaMenu.addEventListener('mouseenter', function () {
                clearTimeout(hoverTimer);
            });

            // Close when mouse leaves the menu
            megaMenu.addEventListener('mouseleave', function (e) {
                // Check if mouse is leaving to outside the nav item
                if (!servicesNavItem.contains(e.relatedTarget)) {
                    hoverTimer = setTimeout(closeMegaMenuFunc, 300); // 300ms delay before closing
                }
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (e) {
                if (isMenuOpen && !servicesNavItem.contains(e.target) && !megaMenu.contains(e.target)) {
                    closeMegaMenuFunc();
                }
            });
        });










                // Service subcategories data
        const serviceSubcategories = {
            residential: [
                "New house construction",
                "Villas & independent homes",
                "Renovation & remodeling",
                "Interior works"
            ],
            industrial: [
                "Industrial sheds",
                "Warehouses",
                "Factories",
                "Godowns",
                "Steel structural buildings"
            ],
            commercial: [
                "Office buildings",
                "Shops & showrooms",
                "Industrial sheds",
                "Retail and warehouse spaces"
            ],
            "interior-exterior": [
                "False ceiling",
                "Wardrobes",
                "Wall décor",
                "Lighting solutions",
                "Exterior elevation",
                "Compound walls",
                "Gate works",
                "Landscaping",
                "Exterior cladding"
            ],
            design: [
                "2D/3D planning",
                "Structural engineering",
                "Elevation design",
                "Approval drawings"
            ],
            fabrication: [
                "Steel / Iron framing",
                "Shed construction & roofing",
                "Steel doors, windows, grills",
                "Metal staircases & railings",
                "Industrial structural fabrication",
                "Fiber roofing and metal truss works"
            ],
            renovation: [
                "Renovate damaged buildings",
                "Redesign old houses"
            ]
        };
        
        // Service icons
        const serviceIcons = {
            residential: "fas fa-home",
            industrial: "fas fa-industry",
            commercial: "fas fa-building",
            "interior-exterior": "fas fa-paint-roller",
            design: "fas fa-drafting-compass",
            fabrication: "fas fa-tools",
            renovation: "fas fa-hammer"
        };
        
        // Service titles
        const serviceTitles = {
            residential: "Residential Construction Services",
            industrial: "Industrial Construction Services",
            commercial: "Commercial Construction Services",
            "interior-exterior": "Interior & Exterior Works",
            design: "Architectural & Structural Design",
            fabrication: "Fabrication Works",
            renovation: "Renovation & Remodeling"
        };
        
        // Checkbox styles for different services
        const checkboxStyles = {
            residential: "style-1",
            industrial: "style-2",
            commercial: "style-3",
            "interior-exterior": "style-4",
            design: "style-5",
            fabrication: "style-1",
            renovation: "style-2"
        };
        
        // Handle service selection change
        document.getElementById('service').addEventListener('change', function() {
            const selectedService = this.value;
            const subcategoryContainer = document.getElementById('subcategory-container');
            
            // Clear previous content
            subcategoryContainer.innerHTML = '';
            
            if (selectedService && serviceSubcategories[selectedService]) {
                // Show the container
                subcategoryContainer.style.display = 'block';
                
                // Create title
                const title = document.createElement('h4');
                title.className = 'subcategory-title';
                title.innerHTML = `<i class="${serviceIcons[selectedService]}"></i> ${serviceTitles[selectedService]}`;
                subcategoryContainer.appendChild(title);
                
                // Create checkbox group
                const checkboxGroup = document.createElement('div');
                checkboxGroup.className = 'checkbox-group';
                
                // Add checkboxes for each subcategory
                serviceSubcategories[selectedService].forEach(subcategory => {
                    const checkboxItem = document.createElement('div');
                    checkboxItem.className = `checkbox-item ${checkboxStyles[selectedService]}`;
                    
                    const checkboxId = `sub-${subcategory.replace(/\s+/g, '-').toLowerCase()}`;
                    
                    checkboxItem.innerHTML = `
                        <input type="checkbox" id="${checkboxId}" name="subcategories" value="${subcategory}">
                        <label for="${checkboxId}" class="checkbox-label">
                            ${subcategory}
                        </label>
                    `;
                    
                    checkboxGroup.appendChild(checkboxItem);
                });
                
                subcategoryContainer.appendChild(checkboxGroup);
            } else {
                // Hide the container if no service is selected
                subcategoryContainer.style.display = 'none';
            }
        });
        
        // Form submission handler
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // In a real application, you would send the form data to a server here
            // For this example, we'll just show the success message
            
            const successMessage = document.getElementById('msgSubmit');
            successMessage.textContent = "Thank you for your enquiry! We'll contact you shortly.";
            successMessage.style.display = 'block';
            
            // Scroll to the success message
            successMessage.scrollIntoView({ behavior: 'smooth' });
            
            // Reset the form after 5 seconds
            setTimeout(function() {
                document.getElementById('contactForm').reset();
                document.getElementById('subcategory-container').style.display = 'none';
                successMessage.style.display = 'none';
            }, 5000);
        });
        
        // Add animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.wow');
            animatedElements.forEach(el => {
                el.style.animation = 'fadeInUp 0.8s ease forwards';
            });
        });