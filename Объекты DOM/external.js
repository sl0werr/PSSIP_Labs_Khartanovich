function calculate(a, b) {
    try {
        if (a > 5 && b > 0) {
            return 3 * (a*a);
        } else if (a > 0 && a <= 5 && b !== 0) {
            return a / b;
        } else if (b === 0) {
            throw new Error("Деление на ноль невозможно");
        } else {
            return b + a - 1;
        }
    } catch (e) {
        alert(`Ошибка: ${e.message}`);
        return "Ошибка в расчетах";
    }
}