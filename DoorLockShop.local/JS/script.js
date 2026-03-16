        document.addEventListener('DOMContentLoaded', function() {
            // Получаем кнопку
            const backToTopButton = document.querySelector('.back-to-top');
            
            // Обработчик скролла
            window.addEventListener('scroll', function() {
                // Показываем/скрываем кнопку
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('active');
                } else {
                    backToTopButton.classList.remove('active');
                }
            });
            
            // Обработчик клика
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Плавная прокрутка вверх
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });



            document.addEventListener('DOMContentLoaded', function() {
            // Находим логотип в футере
            const footerLogo = document.querySelector('.footer-logo');
            
            // Добавляем обработчик клика
            footerLogo.addEventListener('click', function(e) {
                e.preventDefault(); // Предотвращаем стандартное поведение
                
                // Плавный скролл наверх страницы
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Делаем логотип кликабельным (добавляем курсор)
            footerLogo.style.cursor = 'pointer';
        });

    

        document.getElementById('burger').addEventListener('click', function() {
            this.classList.toggle('active');
            document.getElementById('mobileNav').classList.toggle('active');
        });



        document.addEventListener('DOMContentLoaded', function() {
            // Анимация при скролле к футеру
            const footer = document.querySelector('footer');
            
            function checkFooterVisibility() {
                const footerPosition = footer.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if (footerPosition < screenPosition) {
                    footer.style.opacity = '1';
                    footer.style.transform = 'translateY(0)';
                }
            }
            
            // Инициализация стилей для анимации
            footer.style.opacity = '0';
            footer.style.transform = 'translateY(20px)';
            footer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            // Проверка при загрузке и скролле
            checkFooterVisibility();
            window.addEventListener('scroll', checkFooterVisibility);
            
            // Скрипт для копирования контактов
            const contactItems = document.querySelectorAll('.contact-item');
            
            contactItems.forEach(item => {
                item.addEventListener('click', function() {
                    const text = this.innerText.trim();
                    navigator.clipboard.writeText(text).then(() => {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<span class="contact-icon"><i class="fas fa-check"></i></span> <span>Скопировано!</span>';
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                        }, 2000);
                    });
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const nameError = document.getElementById('nameError');
            
            // Валидация имени
            function validateName() {
                if (nameInput.value.trim() === '') {
                    nameError.style.display = 'block';
                    return false;
                } else {
                    nameError.style.display = 'none';
                    return true;
                }
            }
            
            // Валидация в реальном времени
            nameInput.addEventListener('input', function() {
                validateName();
            });
            
            // Валидация при потере фокуса
            nameInput.addEventListener('blur', function() {
                validateName();
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            
            // Валидация email
            function validateEmail() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    emailError.style.display = 'block';
                    return false;
                } else {
                    emailError.style.display = 'none';
                    return true;
                }
            }
            
            // Валидация в реальном времени
            emailInput.addEventListener('input', function() {
                validateEmail();
            });
            
            // Валидация при потере фокуса
            emailInput.addEventListener('blur', function() {
                validateEmail();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('callbackForm');
            const submitBtn = document.getElementById('submitBtn');
            const successMessage = document.getElementById('successMessage');
            
            // Обработчик отправки формы
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Вызываем валидации для обоих полей
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                
                if (isNameValid && isEmailValid) {
                    // Здесь можно добавить AJAX запрос к серверу
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Отправка...';
                    
                    // Имитация отправки данных
                    setTimeout(function() {
                        form.style.display = 'none';
                        successMessage.style.display = 'block';
                        
                        // Очистка формы (для демонстрации)
                        document.getElementById('name').value = '';
                        document.getElementById('email').value = '';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Отправить';
                    }, 1500);
                }
            });
            
            // Объявляем функции валидации глобально для доступа из других скриптов
            window.validateName = function() {
                const nameInput = document.getElementById('name');
                const nameError = document.getElementById('nameError');
                if (nameInput.value.trim() === '') {
                    nameError.style.display = 'block';
                    return false;
                } else {
                    nameError.style.display = 'none';
                    return true;
                }
            };
            
            window.validateEmail = function() {
                const emailInput = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    emailError.style.display = 'block';
                    return false;
                } else {
                    emailError.style.display = 'none';
                    return true;
                }
            };
        });

        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('slider');
            const prevBtn = document.getElementById('prevnBtn');
            const nextBtn = document.getElementById('nextnBtn');
            const cardWidth = 280 + 20; // Ширина карточки + gap
            let scrollPosition = 0;
            const maxScroll = slider.scrollWidth - slider.clientWidth;
            
            // Проверяем, есть ли куда скроллить
            function updateButtons() {
                prevBtn.disabled = scrollPosition <= 0;
                nextBtn.disabled = scrollPosition >= maxScroll;
            }
            
            // Обработчики для кнопок
            prevBtn.addEventListener('click', function() {
                scrollPosition = Math.max(scrollPosition - cardWidth * 2, 0);
                slider.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
                setTimeout(updateButtons, 300);
            });
            
            nextBtn.addEventListener('click', function() {
                scrollPosition = Math.min(scrollPosition + cardWidth * 2, maxScroll);
                slider.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
                setTimeout(updateButtons, 300);
            });
            
            // Инициализация кнопок
            updateButtons();
            
            // Обработчик для скролла мышью
            slider.addEventListener('scroll', function() {
                scrollPosition = slider.scrollLeft;
                updateButtons();
            });
            
            // Адаптация к изменению размера окна
            window.addEventListener('resize', function() {
                maxScroll = slider.scrollWidth - slider.clientWidth;
                updateButtons();
            });
        });

        // Слайдер товаров
        const slider = document.getElementById('productSlider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const dotsContainer = document.getElementById('sliderDots');
        const cards = document.querySelectorAll('.product-card');
        const cardWidth = document.querySelector('.product-card').offsetWidth + 20;
        
        // Создаем точки для навигации
        cards.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                scrollToCard(index);
            });
            dotsContainer.appendChild(dot);
        });
        
        const dots = document.querySelectorAll('.dot');
        
        function scrollToCard(index) {
            slider.scrollTo({
                left: index * cardWidth,
                behavior: 'smooth'
            });
            updateActiveDot(index);
        }
        
        function updateActiveDot(index) {
            dots.forEach(dot => dot.classList.remove('active'));
            dots[index].classList.add('active');
        }
        
        nextBtn.addEventListener('click', () => {
            const scrollPosition = slider.scrollLeft;
            const maxScroll = slider.scrollWidth - slider.clientWidth;
            
            if (scrollPosition < maxScroll) {
                const nextCard = Math.floor((scrollPosition + cardWidth / 2) / cardWidth) + 1;
                scrollToCard(nextCard);
            } else {
                scrollToCard(0);
            }
        });
        
        prevBtn.addEventListener('click', () => {
            const scrollPosition = slider.scrollLeft;
            
            if (scrollPosition > 0) {
                const prevCard = Math.floor((scrollPosition - cardWidth / 2) / cardWidth);
                scrollToCard(prevCard);
            } else {
                scrollToCard(cards.length - 1);
            }
        });
        
        slider.addEventListener('scroll', () => {
            const scrollPosition = slider.scrollLeft;
            const activeCard = Math.round(scrollPosition / cardWidth);
            updateActiveDot(activeCard);
        });

        // Выпадающее меню для мобильных устройств
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggles = document.querySelectorAll('.has-dropdown > a');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        const parent = this.parentElement;
                        const isActive = parent.classList.contains('active');
                        
                        // Закрываем все открытые меню
                        document.querySelectorAll('.has-dropdown').forEach(item => {
                            item.classList.remove('active');
                        });
                        
                        // Открываем текущее, если было закрыто
                        if (!isActive) {
                            parent.classList.add('active');
                        }
                    }
                });
            });
            
            // Закрытие меню при клике вне его
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && !e.target.closest('.has-dropdown')) {
                    document.querySelectorAll('.has-dropdown').forEach(item => {
                        item.classList.remove('active');
                    });
                }
            });
        });