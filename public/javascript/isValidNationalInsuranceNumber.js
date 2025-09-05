// Message to display when the national insurance number is invalid
const rrnErrorMsg = {
    'BE': 'Dit is geen geldig rijksregisternummer',
    'NL': 'Dit is geen geldig burgerservicenummer.',
    'DE': 'Dies ist keine gültige nationale Registernummer.',
    'FR': 'Ce n\'est pas un numéro de registre national valide.',
    'LU': 'Dëst ass keng valabel national Registernummer.',
}

// Check for the national insurance number for following counties:
// 0 = number, X = character, Z = number or character
//
// BE - Belgium: 00.00.00-000.00 + check if valid number
// NL - The Netherlands: 000000000 + check if valid number
// DE - Germany: ZZZZZZZZZ + check if valid number
// FR - France: ZZZZZZZZZZZZ + check if valid number
// LU - Luxembourg: 0000000000000 + check if valid number
const validateId = {
    'BE': (id) => {
        if (!/^[0-9]{2}.[0-9]{2}.[0-9]{2}-[0-9]{3}.[0-9]{2}$/.test(id)) return false;

        const pid = id.replace(/\D/g, '');
        const birthNumber = pid.slice(0, 9);
        const controlNumber = parseInt(pid.slice(9, 11));

        // laatste 2 cijfers van het huidige jaar
        const currentYearTwoDigits = new Date().getFullYear() % 100;

        const adjustedBirthNumber =
            parseInt(pid.slice(0, 2), 10) <= currentYearTwoDigits
                ? `2${birthNumber}`
                : birthNumber;

        const mod97 = 97 - (parseInt(adjustedBirthNumber, 10) % 97);
        return mod97 === controlNumber;
    },
    'NL': (id) => {
        if (!/^\d{8,9}$/.test(id)) return false;
        id = id.padStart(9, '0'); // Voeg voorloopnul toe als nodig

        let sum = 0;
        for (let i = 0; i < 9; i++) {
            let digit = parseInt(id.charAt(i), 10);
            if (i < 8) {
                sum += (9 - i) * digit;
            } else {
                sum += -1 * digit;
            }
        }
        return sum % 11 === 0;
    },
    'DE': (id) => {
        if (!/^[A-Z0-9]{9}$/.test(id)) return false;
        const pid = id.replace(/[^A-Z0-9]/gi, '');

        const weights = [7, 3, 1];
        let sum = 0;

        for (let i = 0; i < pid.length; i++) {
            let char = pid.charAt(i);
            let value;

            if (char >= 'A' && char <= 'Z') {
                value = char.charCodeAt(0) - 'A'.charCodeAt(0) + 10;
            } else if (char >= '0' && char <= '9') {
                value = parseInt(char, 10);
            } else {
                return false; // Invalid character
            }

            sum += value * weights[i % 3];
        }

        return (sum % 10 === 0);
    },
    'FR': (id) => {
        if (!/^[A-Z0-9]{12}$/.test(id)) return false;
        const pid = id.replace(/[^A-Z0-9]/gi, '');

        const weights = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        let sum = 0;

        for (let i = 0; i < id.length; i++) {
            let char = pid.charAt(i);
            let value;

            if (char >= 'A' && char <= 'Z') {
                value = char.charCodeAt(0) - 'A'.charCodeAt(0) + 1;
            } else if (char >= '0' && char <= '9') {
                value = parseInt(char, 10);
            } else {
                return false;
            }

            let weightedValue = value * weights[i];
            if (weightedValue > 9) {
                weightedValue = Math.floor(weightedValue / 10) + (weightedValue % 10);
            }

            sum += weightedValue;
        }

        return (sum % 10 === 0);
    },
    'LU': (id) => {
        if (!/^[0-9]{13}$/.test(id)) return false;

        const birthDate = id.slice(0, 6);
        const sequence = id.slice(6, 10);
        const checksum = parseInt(id.slice(10, 13), 10);
        const calculatedChecksum = 97 - (parseInt(birthDate + sequence, 10) % 97);
        return checksum === calculatedChecksum;
    },
    'XX': () => {
        return true;
    }
}

const formatId = {
    'BE': (id) => {
        let value = id.replace(/\D/g, '');
        let output = '';
        for (let i = 0; i < value.length; i++) {
            if (i === 2 || i === 4) {
                output += '.';
            } else if (i === 6) {
                output += '-';
            } else if (i === 9) {
                output += '.';
            }
            output += value.charAt(i);
        }
        return output.substring(0, 15);
    },
    'NL': (id) => {
        return id.replace(/\D/g, '').substring(0, 9);
    },
    'DE': (id) => {
        return id.replace(/^([A-Z0-9]{9})$/, '$1').substring(0, 9);
    },
    'FR': (id) => {
        return id.replace(/^([A-Z0-9]{12})$/, '$1').substring(0, 12);
    },
    'LU': (id) => {
        return id.replace(/^([0-9]{13})$/, '$1').substring(0, 13);
    },
    'XX': (id) => {
        return id;
    }
}

const birthday = {
    'BE': (nin) => {
        const birthDate = nin.slice(0, 8);
        const year = parseInt(birthDate.slice(0, 2), 10);
        const month = parseInt(birthDate.slice(3, 5), 10).toString().padStart(2, '0');
        const day = parseInt(birthDate.slice(6, 8), 10).toString().padStart(2, '0');
        let date = new Date();
        if (year > date.getFullYear() % 100) {
            return (1900 + year) + '-' + month + '-' + day;
        } else {
            return (2000 + year) + '-' + month + '-' + day;
        }
    },
    'NL': (nin) => {
        return false;
    },
    'DE': (nin) => {
        return false;
    },
    'FR': (nin) => {
        return false;
    },
    'LU': (nin) => {
        const birthDate = nin.slice(0, 6);
        const year = parseInt(birthDate.slice(0, 2), 10);
        const month = parseInt(birthDate.slice(2, 4), 10).toString().padStart(2, '0');
        const day = parseInt(birthDate.slice(4, 6), 10).toString().padStart(2, '0');
        let date = new Date();
        if (year > date.getFullYear() % 100) {
            return (1900 + year) + '-' + month + '-' + day;
        } else {
            return (2000 + year) + '-' + month + '-' + day;
        }
    },
    'XX': (nin) => {
        return false;
    }
}

function isValidNationalInsuranceNumber(nin, country) {
    const validator = validateId[country];
    if (!validator) return false;
    return validator(nin);
}

function formatNationalInsuranceNumber(nin, country) {
    const formatter = formatId[country];
    if (!formatter) return nin;
    return formatter(nin);
}

function getBirthdate(nin, country) {
    const formatter = birthday[country];
    if (!formatter) return '';
    return formatter(nin);
}