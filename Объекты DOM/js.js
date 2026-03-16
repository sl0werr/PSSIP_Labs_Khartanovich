document.addEventListener('DOMContentLoaded', function() {
    // Получаем все элементы формы, которые нужно валидировать
    const formElements = document.querySelectorAll('.custom-select, .input-field');
    
    // Добавляем обработчики событий
    formElements.forEach(element => {
        element.addEventListener('blur', function() {
            validateField(this);
        });
        element.addEventListener('input', function() {
            clearValidation(this);
        });
    });
    
    // Добавляем обработчик для кнопки "Добавить ещё"
    const addMoreBtn = document.querySelector('.hexagon-btn');
    if (addMoreBtn) {
        addMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            validateEducationWorkForm();
        });
    }
    
    // Функция валидации поля
    function validateField(field) {
        // Проверка на обязательность заполнения
        if (field.value.trim() === '' && isRequiredField(field)) {
            markInvalid(field, "Это поле обязательно для заполнения");
            return false;
        }
        
        // Дополнительные проверки для текстовых полей
        if (field.tagName === 'INPUT') {
            // Проверка минимальной длины
            if (field.value.length > 0 && field.value.length < 2) {
                markInvalid(field, "Минимальная длина - 2 символа");
                return false;
            }
            
            // Проверка максимальной длины
            if (field.value.length > 50) {
                markInvalid(field, "Максимальная длина - 50 символов");
                return false;
            }
            
            // Проверка формата для поля "Населенный пункт"
            if (field.placeholder === 'Введите населенный пункт' && !/^[а-яА-ЯёЁ\s-]+$/.test(field.value)) {
                markInvalid(field, "Допустимы только русские буквы, пробелы и дефисы");
                return false;
            }
        }
        
        markValid(field);
        return true;
    }
    
    // Проверка, является ли поле обязательным
    function isRequiredField(field) {
        const subInfo = field.closest('.info-content').querySelector('.sub-info');
        return subInfo && subInfo.textContent.includes('*');
    }
    
    // Функция очистки валидации
    function clearValidation(field) {
        field.classList.remove('error', 'valid');
        const errorMsg = field.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains('error-message')) {
            errorMsg.style.display = 'none';
        }
    }
    
    // Функция валидации всей формы
    function validateEducationWorkForm() {
        let isValid = true;
        
        // Проверяем все обязательные поля
        document.querySelectorAll('.custom-select, .input-field').forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        if (isValid) {
            showSuccessMessage("Форма заполнена корректно!");
            // Здесь можно добавить отправку формы
        } else {
            showErrorMessage("Пожалуйста, исправьте ошибки в форме");
        }
        
        return isValid;
    }
    
    // Функция отметки невалидного поля
    function markInvalid(element, message) {
        element.classList.add('error');
        
        // Создаем или обновляем сообщение об ошибке
        let errorMsg = element.nextElementSibling;
        if (!errorMsg || !errorMsg.classList.contains('error-message')) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            element.parentNode.insertBefore(errorMsg, element.nextSibling);
        }
        
        errorMsg.textContent = message;
        errorMsg.style.display = 'block';
    }
    
    // Функция отметки валидного поля
    function markValid(element) {
        element.classList.add('valid');
        const errorMsg = element.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains('error-message')) {
            errorMsg.style.display = 'none';
        }
    }
    
    // Функция показа сообщения об успехе
    function showSuccessMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'form-message success';
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
    
    // Функция показа сообщения об ошибке
    function showErrorMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'form-message error';
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
});