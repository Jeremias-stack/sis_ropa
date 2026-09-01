import './reportes';


/* =========================================================
   NAVEGACIÓN RESPONSIVE
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const sidebar =
        document.getElementById('appSidebar');

    const overlay =
        document.getElementById('sidebarOverlay');

    const menuButton =
        document.getElementById('mobileMenuButton');


    if (
        sidebar &&
        overlay &&
        menuButton
    ) {

        const openSidebar = () => {

            sidebar.classList.add('show');

            overlay.classList.add('show');

            document.body.style.overflow =
                'hidden';

            menuButton.setAttribute(
                'aria-expanded',
                'true'
            );

        };


        const closeSidebar = () => {

            sidebar.classList.remove('show');

            overlay.classList.remove('show');

            document.body.style.overflow =
                '';

            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );

        };


        menuButton.addEventListener(
            'click',
            () => {

                if (
                    sidebar.classList.contains('show')
                ) {

                    closeSidebar();

                } else {

                    openSidebar();

                }

            }
        );


        overlay.addEventListener(
            'click',
            closeSidebar
        );


        document.addEventListener(
            'keydown',
            (event) => {

                if (
                    event.key === 'Escape' &&
                    sidebar.classList.contains('show')
                ) {

                    closeSidebar();

                }

            }
        );


        sidebar
            .querySelectorAll('a.sidebar-link')
            .forEach((link) => {

                link.addEventListener(
                    'click',
                    () => {

                        if (
                            window.innerWidth < 992
                        ) {

                            closeSidebar();

                        }

                    }
                );

            });


        window.addEventListener(
            'resize',
            () => {

                if (
                    window.innerWidth >= 992
                ) {

                    closeSidebar();

                }

            }
        );

    }


    /* =====================================================
       PUNTO DE VENTA
       ===================================================== */

    const posForm =
        document.getElementById('posForm');


    /*
     * Si no estamos en el POS, terminamos aquí.
     */
    if (!posForm) {
        return;
    }


    const productCards =
        document.querySelectorAll(
            '.pos-product-card'
        );

    const searchInput =
        document.getElementById(
            'posProductSearch'
        );

    const noResults =
        document.getElementById(
            'posNoResults'
        );

    const cartItemsContainer =
        document.getElementById(
            'posCartItems'
        );

    const cartEmpty =
        document.getElementById(
            'posCartEmpty'
        );

    const cartCount =
        document.getElementById(
            'posCartCount'
        );

    const hiddenInputs =
        document.getElementById(
            'posHiddenInputs'
        );

    const subtotalElement =
        document.getElementById(
            'posSubtotal'
        );

    const ivaElement =
        document.getElementById(
            'posIva'
        );

    const discountElement =
        document.getElementById(
            'posDiscount'
        );

    const totalElement =
        document.getElementById(
            'posTotal'
        );

    const discountInput =
        document.getElementById(
            'descuento'
        );

    const checkoutButton =
        document.getElementById(
            'posCheckoutButton'
        );


    let cart = [];


    /* =====================================================
       FORMATO MONEDA
       ===================================================== */

    const money = (value) => {

        return new Intl.NumberFormat(
            'es-GT',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        ).format(value);

    };


    /* =====================================================
       SEGURIDAD PARA TEXTO HTML
       ===================================================== */

    const escapeHtml = (text) => {

        const div =
            document.createElement('div');

        div.textContent =
            text ?? '';

        return div.innerHTML;

    };


    /* =====================================================
       AGREGAR PRODUCTO
       ===================================================== */

    const addProduct = (card) => {

        const id =
            Number(
                card.dataset.productId
            );

        const name =
            card.dataset.productName;

        const code =
            card.dataset.productCode;

        const price =
            Number(
                card.dataset.productPrice
            );

        const stock =
            Number(
                card.dataset.productStock
            );


        const existing =
            cart.find(
                item =>
                    item.id === id
            );


        if (existing) {

            if (
                existing.quantity >= stock
            ) {

                alert(
                    `No hay más existencias disponibles de ${name}.`
                );

                return;

            }


            existing.quantity += 1;

        } else {

            cart.push({

                id,
                name,
                code,
                price,
                stock,
                quantity: 1

            });

        }


        renderCart();

    };


    /* =====================================================
       CAMBIAR CANTIDAD
       ===================================================== */

    const changeQuantity = (
        id,
        amount
    ) => {

        const item =
            cart.find(
                product =>
                    product.id === id
            );


        if (!item) {
            return;
        }


        const newQuantity =
            item.quantity + amount;


        if (
            newQuantity <= 0
        ) {

            removeProduct(id);

            return;

        }


        if (
            newQuantity > item.stock
        ) {

            alert(
                `Solo hay ${item.stock} unidades disponibles de ${item.name}.`
            );

            return;

        }


        item.quantity =
            newQuantity;


        renderCart();

    };


    /* =====================================================
       ELIMINAR PRODUCTO
       ===================================================== */

    const removeProduct = (id) => {

        cart =
            cart.filter(
                item =>
                    item.id !== id
            );


        renderCart();

    };


    /* =====================================================
       CALCULAR TOTALES
       ===================================================== */

    const calculateTotals = () => {

        const subtotal =
            cart.reduce(
                (total, item) =>
                    total +
                    item.price *
                    item.quantity,
                0
            );


        const iva =
            subtotal * 0.12;


        let discount =
            Number(
                discountInput.value
            ) || 0;


        if (
            discount < 0
        ) {

            discount = 0;

        }


        const totalBeforeDiscount =
            subtotal + iva;


        const visualDiscount =
            Math.min(
                discount,
                totalBeforeDiscount
            );


        const total =
            Math.max(
                totalBeforeDiscount -
                visualDiscount,
                0
            );


        subtotalElement.textContent =
            `Q${money(subtotal)}`;


        ivaElement.textContent =
            `Q${money(iva)}`;


        discountElement.textContent =
            `- Q${money(visualDiscount)}`;


        totalElement.textContent =
            `Q${money(total)}`;

    };


    /* =====================================================
       RENDERIZAR CARRITO
       ===================================================== */

    const renderCart = () => {

        cartItemsContainer.innerHTML =
            '';

        hiddenInputs.innerHTML =
            '';


        if (
            cart.length === 0
        ) {

            cartEmpty.style.display =
                'block';

            cartItemsContainer.appendChild(
                cartEmpty
            );

            checkoutButton.disabled =
                true;

        } else {

            cartEmpty.style.display =
                'none';

            checkoutButton.disabled =
                false;

        }


        let quantityTotal = 0;


        cart.forEach(
            (item, index) => {

                quantityTotal +=
                    item.quantity;


                const row =
                    document.createElement(
                        'div'
                    );


                row.className =
                    'pos-cart-item';


                row.innerHTML = `

                    <div>

                        <span class="pos-cart-item-name">
                            ${escapeHtml(item.name)}
                        </span>

                        <span class="pos-cart-item-price">

                            ${escapeHtml(item.code)}
                            ·
                            Q${money(item.price)}
                            c/u

                        </span>


                        <div class="pos-cart-controls">

                            <button
                                type="button"
                                class="pos-quantity-button"
                                data-action="minus"
                                data-id="${item.id}"
                            >

                                <i class="bi bi-dash"></i>

                            </button>


                            <span class="pos-quantity-value">

                                ${item.quantity}

                            </span>


                            <button
                                type="button"
                                class="pos-quantity-button"
                                data-action="plus"
                                data-id="${item.id}"
                            >

                                <i class="bi bi-plus"></i>

                            </button>


                            <button
                                type="button"
                                class="pos-remove-item"
                                data-action="remove"
                                data-id="${item.id}"
                                title="Eliminar"
                            >

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>

                    </div>


                    <div class="pos-cart-item-subtotal">

                        Q${money(
                            item.price *
                            item.quantity
                        )}

                    </div>

                `;


                cartItemsContainer.appendChild(
                    row
                );


                /* Producto */
                const productInput =
                    document.createElement(
                        'input'
                    );

                productInput.type =
                    'hidden';

                productInput.name =
                    `items[${index}][producto_id]`;

                productInput.value =
                    item.id;


                /* Cantidad */
                const quantityInput =
                    document.createElement(
                        'input'
                    );

                quantityInput.type =
                    'hidden';

                quantityInput.name =
                    `items[${index}][cantidad]`;

                quantityInput.value =
                    item.quantity;


                hiddenInputs.appendChild(
                    productInput
                );

                hiddenInputs.appendChild(
                    quantityInput
                );

            }
        );


        cartCount.textContent =
            quantityTotal;


        calculateTotals();

    };


    /* =====================================================
       EVENTOS PRODUCTOS
       ===================================================== */

    productCards.forEach(
        (card) => {

            card.addEventListener(
                'click',
                () => {

                    addProduct(card);

                }
            );


            card.addEventListener(
                'keydown',
                (event) => {

                    if (
                        event.key === 'Enter' ||
                        event.key === ' '
                    ) {

                        event.preventDefault();

                        addProduct(card);

                    }

                }
            );

        }
    );


    /* =====================================================
       EVENTOS CARRITO
       ===================================================== */

    cartItemsContainer.addEventListener(
        'click',
        (event) => {

            const button =
                event.target.closest(
                    '[data-action]'
                );


            if (!button) {
                return;
            }


            const id =
                Number(
                    button.dataset.id
                );


            const action =
                button.dataset.action;


            if (
                action === 'plus'
            ) {

                changeQuantity(
                    id,
                    1
                );

            }


            if (
                action === 'minus'
            ) {

                changeQuantity(
                    id,
                    -1
                );

            }


            if (
                action === 'remove'
            ) {

                removeProduct(id);

            }

        }
    );


    /* =====================================================
       BUSCADOR
       ===================================================== */

    searchInput.addEventListener(
        'input',
        () => {

            const term =
                searchInput.value
                    .trim()
                    .toLowerCase();


            let visible = 0;


            productCards.forEach(
                (card) => {

                    const name =
                        card.dataset.productName
                            .toLowerCase();


                    const code =
                        card.dataset.productCode
                            .toLowerCase();


                    const matches =
                        name.includes(term) ||
                        code.includes(term);


                    card.style.display =
                        matches
                            ? ''
                            : 'none';


                    if (matches) {
                        visible++;
                    }

                }
            );


            noResults.style.display =
                visible === 0
                    ? 'block'
                    : 'none';

        }
    );


    /* =====================================================
       DESCUENTO
       ===================================================== */

    discountInput.addEventListener(
        'input',
        calculateTotals
    );


    /* =====================================================
       VALIDAR ENVÍO
       ===================================================== */

    posForm.addEventListener(
        'submit',
        (event) => {

            if (
                cart.length === 0
            ) {

                event.preventDefault();

                alert(
                    'Agrega al menos un producto antes de cobrar.'
                );

                return;

            }


            const subtotal =
                cart.reduce(
                    (total, item) =>
                        total +
                        item.price *
                        item.quantity,
                    0
                );


            const iva =
                subtotal * 0.12;


            const totalBeforeDiscount =
                subtotal + iva;


            const discount =
                Number(
                    discountInput.value
                ) || 0;


            if (
                discount >
                totalBeforeDiscount
            ) {

                event.preventDefault();

                alert(
                    'El descuento no puede ser mayor al total de la venta.'
                );

                return;

            }


            checkoutButton.disabled =
                true;


            checkoutButton.innerHTML = `

                <span
                    class="spinner-border spinner-border-sm"
                ></span>

                Procesando venta...

            `;

        }
    );


    renderCart();

});

/* =========================================================
   MOSTRAR / OCULTAR CONTRASEÑA - LOGIN
   ========================================================= */

const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
const togglePasswordIcon = document.getElementById('togglePasswordIcon');

if (
    passwordInput &&
    togglePassword &&
    togglePasswordIcon
) {

    togglePassword.addEventListener('click', () => {

        const mostrando =
            passwordInput.type === 'text';

        passwordInput.type =
            mostrando
                ? 'password'
                : 'text';

        togglePasswordIcon.className =
            mostrando
                ? 'bi bi-eye'
                : 'bi bi-eye-slash';


        togglePassword.setAttribute(
            'aria-label',
            mostrando
                ? 'Mostrar contraseña'
                : 'Ocultar contraseña'
        );

    });

}