<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Comprueba que el valor sea un ISBN-10 o ISBN-13 válido: formato
 * correcto (se admiten guiones y espacios) y dígito verificador
 * calculado con el algoritmo oficial de cada estándar.
 */
class Isbn implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $limpio = strtoupper(preg_replace('/[\s-]+/', '', (string) $value));

        if (preg_match('/^\d{13}$/', $limpio) === 1) {
            if (! in_array(substr($limpio, 0, 3), ['978', '979'], true)) {
                $fail('El ISBN debe ser un ISBN-10 o un ISBN-13 (los ISBN-13 empiezan por 978 o 979).');

                return;
            }

            $suma = 0;

            for ($i = 0; $i < 13; $i++) {
                $suma += (int) $limpio[$i] * ($i % 2 === 0 ? 1 : 3);
            }

            if ($suma % 10 !== 0) {
                $fail('El ISBN no es válido: el dígito verificador no coincide.');
            }

            return;
        }

        if (preg_match('/^\d{9}[\dX]$/', $limpio) === 1) {
            $suma = 0;

            for ($i = 0; $i < 10; $i++) {
                $digito = $limpio[$i] === 'X' ? 10 : (int) $limpio[$i];
                $suma += $digito * (10 - $i);
            }

            if ($suma % 11 !== 0) {
                $fail('El ISBN no es válido: el dígito verificador no coincide.');
            }

            return;
        }

        $fail('El ISBN no tiene un formato válido. Debe ser un ISBN-10 o ISBN-13 (se admiten guiones y espacios).');
    }
}
