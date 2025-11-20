<?php
require_once __DIR__ . '/helpers.php';

function products_path(): string
{
    return __DIR__ . '/../data/products.json';
}

function load_products(): array
{
    return load_json(products_path());
}

function save_products(array $products): void
{
    save_json(products_path(), $products);
}

function index_products_by_id(): array
{
    $indexed = [];
    foreach (load_products() as $product) {
        $indexed[$product['id']] = $product;
    }
    return $indexed;
}

function create_product(string $name, float $price, string $description, string $category, string $image): array
{
    $products = load_products();
    $product = [
        'id' => generate_id(),
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'category' => $category,
        'image' => $image,
        'created_at' => date('c'),
    ];
    $products[] = $product;
    save_products($products);
    return $product;
}
