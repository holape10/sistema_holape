<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
}
.header-kiosko {
    background-color: #3498db;
    color: white;
    padding: 15px;
    text-align: center;
    font-size: 1.8em;
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-kiosko .back-button {
    background: none;
    border: none;
    color: white;
    font-size: 1em;
    cursor: pointer;
    padding: 5px;
}
.main-content {
    display: flex;
    flex-wrap: wrap;
}
.menu-section {
    flex: 2;
    padding: 10px;
    border-right: 1px solid #eee;
}
.cart-section {
    flex: 1;
    padding: 10px;
    background-color: #e9ecef;
    border-radius: 8px;
}
.category-buttons-container-kiosko {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 8px;
    padding: 5px;
    margin-bottom: 15px;
}
.btn-category-kiosko {
    height: 60px;
    padding: 0 20px;
    font-size: 1.1em;
    font-weight: bold;
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    white-space: nowrap;
    min-width: 90px;
    border: none;
}
.btn-category-kiosko:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.15);
    opacity: 0.9;
}
.btn-category-kiosko.active {
    border: 3px solid #f0ad4e;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
}

.product-search-box-kiosko {
    padding: 10px;
    background-color: #6c757d;
    color: white;
    font-weight: bold;
    text-align: center;
    border-radius: 5px;
    margin-bottom: 15px;
}
.product-search-box-kiosko input {
    border-radius: 5px;
    padding: 10px;
    font-size: 1.1em;
    border: none;
    width: 100%;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 15px;
    padding: 5px;
    max-height: calc(100vh - 350px);
    overflow-y: auto;
}
.product-item-kiosko {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 5px;
    background-color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: transform 0.1s ease, box-shadow 0.1s ease;
    cursor: pointer;
    overflow: hidden;
    height: 200px;
}
.product-item-kiosko:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.15);
}
.product-item-kiosko .product-img-container {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 65%;
}
.product-item-kiosko .product-img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}
.product-item-kiosko .product-details-kiosko {
    flex-shrink: 0;
    width: 100%;
    padding-top: 5px;
}

/* Mejoras para nombre largo, precio y stock */
.product-item-kiosko .product-name-kiosko {
    font-size: 1em;
    font-weight: bold;
    line-height: 1.2;
    max-height: 3.6em; /* 3 líneas aprox en PC */
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    white-space: normal;
    margin-bottom: 4px;
    color: #222;
}
.product-item-kiosko .product-price-kiosko {
    font-size: 1.15em;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 2px;
}
.product-item-kiosko .product-stock-kiosko {
    font-size: 0.85em;
    color: #28a745;
    font-weight: bold;
    margin-bottom: 2px;
}
.product-item-kiosko.agotado .product-stock-kiosko {
    color: #E74C3C;
}

/* Responsive para móviles */
@media (max-width: 991px) {
    .menu-section, .cart-section {
        flex-basis: 100%;
        border-right: none;
        margin-bottom: 20px;
    }
    .menu-section {
        order: 2;
    }
    .cart-section {
        order: 1;
    }
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        max-height: 400px;
    }
    .product-item-kiosko {
        height: 170px;
    }
    .cart-items-container {
        max-height: 300px;
    }
}

/* ESTILOS OPTIMIZADOS PARA MÓVILES (max-width: 576px) */
@media (max-width: 576px) {
    .header-kiosko {
        font-size: 1.5em;
        padding: 10px;
    }
    .btn-category-kiosko {
        height: 40px;
        font-size: 0.9em;
        padding: 0 10px;
        min-width: 70px;
    }
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }
    .product-item-kiosko {
        height: 180px;
        padding: 3px;
    }
    .product-item-kiosko .product-name-kiosko {
        font-size: 0.95em;
        -webkit-line-clamp: 4;
        max-height: 5.0em;
        white-space: normal;
        margin-bottom: 2px;
    }
    .product-item-kiosko .product-price-kiosko {
        font-size: 1.05em;
    }
    .product-item-kiosko .product-stock-kiosko {
        font-size: 0.8em;
    }
    .cart-header {
        font-size: 1.4em;
    }
    .cart-item {
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .cart-item-name {
        flex-basis: 100%;
        margin-bottom: 5px;
    }
    .cart-item-details {
        flex-basis: auto;
        flex-grow: 1;
        margin-left: 0;
        justify-content: flex-start;
        order: 2;
    }
    .cart-item-qty-control {
        margin-left: 0;
    }
    .cart-item-unit-price {
        margin-left: 5px;
    }
    .cart-item-total-price {
        flex-basis: auto;
        margin-left: 10px;
        order: 3;
    }
    .cart-item-remove {
        margin-left: auto;
        align-self: center;
        order: 1;
    }
    .cart-item-obs {
        flex-basis: 100%;
        margin-top: 10px;
        order: 4;
    }
    .cart-actions {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        gap: 5px;
        margin-top: 15px;
    }
    .cart-actions .btn {
        font-size: 1.0em;
        padding: 10px 5px;
        margin: 0;
        border-radius: 6px;
        flex-grow: 1;
        width: 50%;
        min-width: unset;
    }
}

.cart-header {
    font-size: 1.6em;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
    position: sticky;
    top: 0;
    background-color: #e9ecef;
    z-index: 10;
    padding-bottom: 10px;
}
.cart-items-container {
    max-height: calc(100vh - 350px);
    overflow-y: auto;
    border-bottom: 1px solid #ccc;
    padding-bottom: 10px;
    margin-bottom: 10px;
}
.cart-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    background-color: #fff;
    padding: 8px;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.cart-item-name {
    flex-grow: 1;
    font-size: 1.1em;
    font-weight: bold;
    color: #333;
    padding-right: 5px;
    margin-bottom: 0px;
}
.cart-item-details {
    display: flex;
    flex-direction: row;
    align-items: center;
    margin-left: 10px;
}
.cart-item-qty-control {
    display: flex;
    align-items: center;
    margin-bottom: 0px;
}
.cart-item-qty-control input {
    width: 50px;
    text-align: center;
    font-size: 1em;
    margin: 0 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 5px 0;
}
.cart-item-qty-control .btn-qty {
    padding: 3px 8px;
    font-size: 1.1em;
    line-height: 1;
    border-radius: 4px;
    background-color: #007bff;
    color: white;
    border: none;
}
.cart-item-unit-price {
    width: 70px;
    text-align: center;
    font-size: 1em;
    margin-left: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 5px 0;
    box-sizing: border-box;
}
.cart-item-total-price {
    font-weight: bold;
    font-size: 1.1em;
    white-space: nowrap;
    margin-left: 10px;
    color: #007bff;
}
.cart-item-remove {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 5px;
    margin-left: auto;
    align-self: center;
}
.cart-item-remove.disabled-old-item {
    background-color: #f0ad4e;
    cursor: pointer;
    opacity: 0.8;
}
.cart-item-obs {
    flex-basis: 100%;
    margin-top: 10px;
    margin-bottom: 0;
}

.cart-total {
    font-size: 1.8em;
    font-weight: bold;
    text-align: right;
    margin-top: 15px;
    color: #28a745;
}
.cart-actions {
    text-align: center;
    margin-top: 20px;
}
.cart-actions .btn {
    font-size: 1.5em;
    padding: 15px 30px;
    margin: 5px;
    border-radius: 8px;
}
.cart-actions .btn-send-order {
    background-color: #28a745;
    color: white;
}
.cart-actions .btn-clear-cart {
    background-color: #dc3545;
    color: white;
}

input[type="number"] {
    -moz-appearance: textfield;
}
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.swal2-input, .swal2-textarea {
    margin-top: 10px !important;
    margin-bottom: 10px !important;
}
.swal2-label {
    font-weight: bold;
    display: block;
    text-align: left;
    margin-top: 10px;
}
.product-item-kiosko.agotado {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f2f2f2;
    filter: grayscale(100%);
}
.product-item-kiosko.agotado .product-stock-kiosko {
    color: #E74C3C;
    font-weight: bold;
}
</style>