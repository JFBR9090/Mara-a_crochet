<?php
declare(strict_types=1);

namespace Marana\Controllers;

use Marana\Http\Response;
use Marana\Services\ProductService;
use Throwable;

/**
 * Controlador REST para el catálogo de productos y amigurumis
 */
class ProductController {
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(): void {
        try {
            $id = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
            if ($id !== '') {
                $product = $this->productService->getProductById($id);
                if (!$product) {
                    Response::error("Producto '{$id}' no encontrado", 404);
                    return;
                }
                Response::json(['success' => true, 'product' => $product->toArray()]);
                return;
            }

            $products = $this->productService->getCatalog();
            Response::json([
                'success'  => true,
                'products' => array_map(fn($p) => $p->toArray(), $products)
            ]);
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function update(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['id'])) {
                Response::error("El ID del producto es obligatorio", 400);
                return;
            }

            $id = (string)$input['id'];
            $updated = null;

            if (isset($input['stock'])) {
                $updated = $this->productService->adjustStock($id, (int)$input['stock']);
            }

            if (isset($input['price'])) {
                $orig = isset($input['originalPrice']) ? (int)$input['originalPrice'] : null;
                $updated = $this->productService->adjustPrice($id, (int)$input['price'], $orig);
            }

            if (!$updated) {
                Response::error("No se especificaron cambios válidos (stock o price)", 400);
                return;
            }

            Response::json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'product' => $updated->toArray()
            ]);
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
