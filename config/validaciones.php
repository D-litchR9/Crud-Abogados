<?php
function validar_campo(string $valor, string $tipo, string $etiqueta, bool $obligatorio = true): ?string
{
    $valor = trim($valor);

    if ($valor === '') {
        return $obligatorio ? "El campo «{$etiqueta}» es obligatorio." : null;
    }

    switch ($tipo) {
        case 'letras':
            if (!preg_match('/^[\p{L}\s]{2,80}$/u', $valor)) return "«{$etiqueta}» Solo admite letras y espacios.";
            break;
        case 'alfanumerico':
            if (!preg_match('/^[\p{L}\p{N}\s]{2,100}$/u', $valor)) return "«{$etiqueta}»Solo admite letras y números.";
            break;
        case 'numero':
            if (!ctype_digit($valor)) return "«{$etiqueta}» Solo admite números.";
            break;
        case 'telefono':
            if (!preg_match('/^\+?[\d\s]{7,15}$/', $valor)) return "«{$etiqueta}» No es un teléfono válido.";
            break;
        case 'email':
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) return "«{$etiqueta}» No es un correo válido.";
            break;
        case 'password':
            if (strlen($valor) < 8 || !preg_match('/[A-Z]/', $valor)
                || !preg_match('/[a-z]/', $valor) || !preg_match('/\d/', $valor))
                return "«{$etiqueta}» Debe tener mínimo 8 caracteres, mayúscula, minúscula y número.";
            break;
        case 'fecha':
            $d = DateTime::createFromFormat('Y-m-d', $valor);
            if (!$d || $d->format('Y-m-d') !== $valor) return "«{$etiqueta}» No es una fecha válida.";
            break;
        case 'texto':
        if (!preg_match('/^[\p{L}\p{N}\s.,;:()\-_\/#°"\'¿?¡!]{3,150}$/u', $valor))
            return "«{$etiqueta}» Contiene caracteres no permitidos (3 a 150 caracteres).";
        break;
    }
    return null;
}

/** valor, tipo, etiqueta, obligatorio? */
function validar_campos(array $reglas): ?string
{
    foreach ($reglas as $r) {
        $error = validar_campo($r[0] ?? '', $r[1], $r[2], $r[3] ?? true);
        if ($error) return $error;
    }
    return null;
}

/* notificación */

function notificaciones(?string $error = null, ?string $exito = null): void
{
    ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer" style="z-index:1100"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showToast(message, type = 'danger') {
        const icons = { danger: 'fa-circle-exclamation', success: 'fa-circle-check', warning: 'fa-triangle-exclamation' };
        const el = document.createElement('div');
        el.className = `toast align-items-center text-bg-${type} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body"><i class="fa-solid ${icons[type]} me-2"></i></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        el.querySelector('.toast-body').append(document.createTextNode(message));
        document.getElementById('toastContainer').appendChild(el);
        new bootstrap.Toast(el, { delay: 4500 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    const REGLAS = {
        letras:       { ok: v => /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,80}$/.test(v),      msg: 'Solo admite letras y espacios' },
        alfanumerico: { ok: v => /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s]{2,100}$/.test(v), msg: 'Solo admite letras y números' },
        numero:       { ok: v => /^\d+$/.test(v),                                 msg: 'Solo admite números' },
        telefono: { ok: v => /^\+?[\d\s]{7,15}$/.test(v), msg: 'no es un teléfono válido' },
        email:        { ok: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v),         msg: 'No es un correo válido' },
        password:     { ok: v => v.length >= 8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /\d/.test(v),
                        msg: 'Debe tener mínimo 8 caracteres, mayúscula, minúscula y número' },
        fecha:        { ok: v => /^\d{4}-\d{2}-\d{2}$/.test(v) && !isNaN(Date.parse(v)), msg: 'No es una fecha válida' }
        texto:    { ok: v => /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s.,;:()\-_\/#°"'¿?¡!]{3,150}$/.test(v), msg: 'Contiene caracteres no permitidos (3 a 150 caracteres)' },
    };

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('validar-form')) return;

        for (const campo of form.querySelectorAll('[data-validar]')) {
            const valor = campo.value.trim();
            const etiqueta = campo.dataset.label || campo.name;

            if (valor === '') {
                if (campo.required) { e.preventDefault(); return showToast(`El campo «${etiqueta}» es obligatorio.`); }
                continue;
            }
            const regla = REGLAS[campo.dataset.validar];
            if (regla && !regla.ok(valor)) { e.preventDefault(); return showToast(`«${etiqueta}» ${regla.msg}.`); }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($error)): ?>showToast(<?php echo json_encode($error); ?>, 'danger');<?php endif; ?>
        <?php if (!empty($exito)): ?>showToast(<?php echo json_encode($exito); ?>, 'success');<?php endif; ?>
    });
    </script>
    <?php
}