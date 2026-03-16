// Меню бургер (1)
document.addEventListener('DOMContentLoaded', function() {
    const burgerBtn = document.querySelector('.burger-btn');
    const headerLinks = document.querySelector('.header_links');
    const headerGroup = document.querySelector('.header_group');

    // Создаем мобильное меню
    const mobileMenu = document.createElement('div');
    mobileMenu.classList.add('mobile-menu');
    document.body.appendChild(mobileMenu);

    // Клонируем элементы навигации для мобильного меню
    const mobileLinks = headerLinks.cloneNode(true);
    const mobileGroup = headerGroup.cloneNode(true);

    // Добавляем их в мобильное меню
    mobileMenu.innerHTML = '';
    mobileMenu.appendChild(mobileLinks);
    mobileMenu.appendChild(mobileGroup);

    // Обработчик клика по бургеру
    burgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        burgerBtn.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        document.body.classList.toggle('menu-open');
    });

    // Закрываем меню при клике на ссылку
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            burgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            document.body.classList.remove('menu-open');
        });
    });

    // Закрываем меню при клике вне его
    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && e.target !== burgerBtn) {
            burgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
    });
});

//Instagramm + Twitter (2)
document.addEventListener('DOMContentLoaded', function() {
// Находим все ссылки на соцсети в футере
const socialLinks = document.querySelectorAll('.footer_list-item a');

// Добавляем обработчики клика
socialLinks.forEach(link => {
link.addEventListener('click', function(e) {
    // Проверяем, является ли ссылка на соцсеть
    if (this.href.includes('instagram.com') || this.href.includes('twitter.com')) {
        e.preventDefault(); // Отменяем стандартное поведение
        
        // Открываем ссылку в новом окне
        window.open(this.href, '_blank');
        
        // Альтернативный вариант - переход в текущем окне
        // window.location.href = this.href;
    }
});
});
});

//(3) Плавная прокрутка при клике на якорные ссылки
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
});

//(4)  Валидация формы перед отправкой
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--c-red)';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            // Validate email format
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && !/^\S+@\S+\.\S+$/.test(emailField.value)) {
                emailField.style.borderColor = 'var(--c-red)';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            } else {
                // Form is valid, could show success message here
                console.log('Form submitted successfully');
            }
        });
    }
});

//(5) Анимация при скролле (появление элементов)
document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.main_product-item, .header_description, .main_intro-title');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    animateElements.forEach(element => {
        observer.observe(element);
    });
});

//(6)Ленивая загрузка изображений
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
});

//(6) Темный/светлый режим переключения
document.addEventListener('DOMContentLoaded', function() {
    const modeToggle = document.createElement('button');
    modeToggle.className = 'btn-main';
    modeToggle.style.position = 'fixed';
    modeToggle.style.bottom = '20px';
    modeToggle.style.right = '20px';
    modeToggle.style.zIndex = '1000';
    modeToggle.textContent = '🌙';
    modeToggle.title = 'Toggle dark mode';
    document.body.appendChild(modeToggle);

    modeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        this.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
        
        // Save preference to localStorage
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });

    // Check for saved preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        modeToggle.textContent = '☀️';
    }
});

//(7) Обратный отсчет до специального предложения
document.addEventListener('DOMContentLoaded', function() {
    const countdownContainer = document.createElement('div');
    countdownContainer.style.position = 'fixed';
    countdownContainer.style.bottom = '70px';
    countdownContainer.style.right = '20px';
    countdownContainer.style.backgroundColor = 'var(--c-primary)';
    countdownContainer.style.color = 'white';
    countdownContainer.style.padding = '10px 15px';
    countdownContainer.style.borderRadius = '5px';
    countdownContainer.style.zIndex = '1000';
    countdownContainer.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    document.body.appendChild(countdownContainer);

    const endDate = new Date();
    endDate.setHours(endDate.getHours() + 24); // 24 hours from now

    function updateCountdown() {
        const now = new Date();
        const diff = endDate - now;

        if (diff <= 0) {
            countdownContainer.innerHTML = 'Special offer expired!';
            return;
        }

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdownContainer.innerHTML = `Special offer ends in:<br>${hours}h ${minutes}m ${seconds}s`;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});

//(8) Кнопка наверх
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.createElement('button');
    backToTopBtn.textContent = '↑';
    backToTopBtn.style.position = 'fixed';
    backToTopBtn.style.bottom = '20px';
    backToTopBtn.style.left = '20px'; // Изменено с right на left
    backToTopBtn.style.width = '40px';
    backToTopBtn.style.height = '40px';
    backToTopBtn.style.borderRadius = '50%';
    backToTopBtn.style.backgroundColor = 'var(--c-primary)';
    backToTopBtn.style.color = 'white';
    backToTopBtn.style.border = 'none';
    backToTopBtn.style.cursor = 'pointer';
    backToTopBtn.style.display = 'none';
    backToTopBtn.style.zIndex = '999';
    document.body.appendChild(backToTopBtn);

    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });

    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

//(9) Анимация логотипа
document.addEventListener('DOMContentLoaded', function() {
    const logo = document.querySelector('.header_logo');
    if (logo) {
        logo.addEventListener('mouseenter', function() {
            this.style.transition = 'transform 0.5s ease';
            this.style.transform = 'rotate(15deg)';
        });
        logo.addEventListener('mouseleave', function() {
            this.style.transform = 'rotate(0)';
        });
    }
});
