(function (window) {
    "use strict";

    const compact = (value) => String(value ?? "")
        .toUpperCase()
        .replace(/[^0-9A-Z]/g, "");

    const cpfDigits = (value) => String(value ?? "").replace(/\D/g, "");

    const formatParts = (value, separators) => {
        const parts = [];
        let cursor = 0;
        separators.forEach(([length, separator]) => {
            const part = value.slice(cursor, cursor + length);
            if (part) parts.push(part);
            cursor += length;
            if (part.length === length && cursor < value.length) parts.push(separator);
        });
        const remainder = value.slice(cursor);
        if (remainder) parts.push(remainder);
        return parts.join("");
    };

    const formatCpf = (value) => formatParts(cpfDigits(value).slice(0, 11), [
        [3, "."], [3, "."], [3, "-"]
    ]);

    const formatCnpj = (value) => formatParts(compact(value).slice(0, 14), [
        [2, "."], [3, "."], [3, "/"], [4, "-"]
    ]);

    const cpf = (value) => {
        const digits = cpfDigits(value);
        if (digits.length !== 11 || /^(\d)\1+$/.test(digits)) return false;

        for (let length = 9; length <= 10; length += 1) {
            const sum = digits.slice(0, length).split("").reduce(
                (total, digit, index) => total + Number(digit) * (length + 1 - index), 0
            );
            const expected = ((sum * 10) % 11) % 10;
            if (expected !== Number(digits[length])) return false;
        }

        return true;
    };

    const cnpj = (value) => {
        const normalized = compact(value);
        if (!/^[0-9A-Z]{12}[0-9]{2}$/.test(normalized) || /^(.)\1+$/.test(normalized)) return false;

        const calculate = (length, firstWeight) => {
            let weight = firstWeight;
            const sum = normalized.slice(0, length).split("").reduce((total, character) => {
                const numericValue = character.charCodeAt(0) - 48;
                const result = total + numericValue * weight;
                weight = weight === 2 ? 9 : weight - 1;
                return result;
            }, 0);
            const remainder = sum % 11;
            return remainder < 2 ? 0 : 11 - remainder;
        };

        return calculate(12, 5) === Number(normalized[12])
            && calculate(13, 6) === Number(normalized[13]);
    };

    window.FokusDocuments = { compact, cpf, cnpj, formatCpf, formatCnpj };
})(window);
