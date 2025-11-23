<?php
require_once __DIR__ . '/helpers.php';

function products_path(): string
{
    return __DIR__ . '/../data/products.json';
}

function load_products(): array
{
    $products = load_json(products_path());
    return array_map(function ($product) {
        $product['stock'] = $product['stock'] ?? 25;
        $product['status'] = $product['status'] ?? 'active';
        return $product;
    }, $products);
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

function create_product(string $name, float $price, string $description, string $category, string $image, int $stock = 25, string $status = 'active'): array
{
    $products = load_products();
    $product = [
        'id' => generate_id(),
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'category' => $category,
        'image' => $image,
        'stock' => $stock,
        'status' => $status,
        'created_at' => date('c'),
    ];
    $products[] = $product;
    save_products($products);
    return $product;
}

function update_product(string $id, array $payload): array
{
    $products = load_products();
    foreach ($products as &$product) {
        if ($product['id'] === $id) {
            $product = array_merge($product, [
                'name' => trim($payload['name'] ?? $product['name']),
                'price' => isset($payload['price']) ? (float)$payload['price'] : $product['price'],
                'description' => trim($payload['description'] ?? $product['description']),
                'category' => trim($payload['category'] ?? $product['category']),
                'image' => trim($payload['image'] ?? $product['image']),
                'stock' => isset($payload['stock']) ? (int)$payload['stock'] : ($product['stock'] ?? 0),
                'status' => $payload['status'] ?? ($product['status'] ?? 'active'),
            ]);
            save_products($products);
            return $product;
        }
    }

    throw new RuntimeException('Product not found');
}

function delete_product(string $id): void
{
    $products = array_values(array_filter(load_products(), fn($p) => $p['id'] !== $id));
    save_products($products);
}
