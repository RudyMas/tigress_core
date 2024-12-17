function isValidIBAN(iban) {
    // Remove spaces and make the string uppercase
    iban = iban.replace(/\s+/g, '').toUpperCase();

    // Basic check for the length of the IBAN
    const ibanLengths = {
        'AL': 28, 'AD': 24, 'AT': 20, 'AZ': 28, 'BH': 22, 'BE': 16, 'BA': 20, 'BR': 29, 'BG': 22,
        'CR': 22, 'HR': 21, 'CY': 28, 'CZ': 24, 'DK': 18, 'DO': 28, 'EE': 20, 'FO': 18, 'FI': 18,
        'FR': 27, 'GE': 22, 'DE': 22, 'GI': 23, 'GR': 27, 'GL': 18, 'GT': 28, 'HU': 28, 'IS': 26,
        'IE': 22, 'IL': 23, 'IT': 27, 'JO': 30, 'KZ': 20, 'KW': 30, 'LV': 21, 'LB': 28, 'LI': 21,
        'LT': 20, 'LU': 20, 'MT': 31, 'MR': 27, 'MU': 30, 'MC': 27, 'MD': 24, 'ME': 22, 'NL': 18,
        'NO': 15, 'PK': 24, 'PS': 29, 'PL': 28, 'PT': 25, 'QA': 29, 'RO': 24, 'SM': 27, 'SA': 24,
        'RS': 22, 'SK': 24, 'SI': 19, 'ES': 24, 'SE': 24, 'CH': 21, 'TN': 24, 'TR': 26, 'AE': 23,
        'GB': 22, 'VG': 24
    };

    const countryCode = iban.slice(0, 2);
    if (!ibanLengths[countryCode] || iban.length !== ibanLengths[countryCode]) {
        return false;
    }

    // Move the first four characters to the end
    iban = iban.slice(4) + iban.slice(0, 4);

    // Convert letters to numbers (A = 10, B = 11, ..., Z = 35)
    const convertedIBAN = iban.split('').map(char => {
        if (char >= 'A' && char <= 'Z') {
            return char.charCodeAt(0) - 55; // A = 10, B = 11, ..., Z = 35
        } else {
            return char;
        }
    }).join('');

    // Perform mod 97 operation
    const remainder = BigInt(convertedIBAN) % BigInt(97);
    return remainder === BigInt(1);
}