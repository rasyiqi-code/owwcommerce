<?php
namespace OwwCommerce\Models;

/**
 * Class Product Entity
 *
 * Merepresentasikan baris dalam tabel oww_products.
 * Mendukung gambar produk via field image_url.
 */
class Product {
    public ?int $id;
    public string $title;
    public string $slug;
    public string $description;
    public string $type;
    public string $status;
    public float $price;
    public ?float $sale_price;
    public ?string $sku;
    public int $stock_qty;
    /** URL gambar utama produk */
    public ?string $image_url;
    public array $gallery_ids = [];
    public int $sales_count = 0;
    public ?string $upsell_ids;
    public ?string $cross_sell_ids;
    public ?string $created_at;
    public ?string $updated_at;

    /** @var ProductVariation[] Daftar variasi produk */
    public array $variations = [];

    public function __construct( array $data = [] ) {
        $this->id          = isset( $data['id'] ) ? (int) $data['id'] : null;
        $this->title       = $data['title'] ?? '';
        $this->slug        = $data['slug'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->type        = $data['type'] ?? 'simple';
        $this->status      = $data['status'] ?? 'publish';
        $this->price       = isset( $data['price'] ) ? (float) $data['price'] : 0.00;
        $this->sale_price  = isset( $data['sale_price'] ) ? (float) $data['sale_price'] : null;
        $this->sku         = $data['sku'] ?? null;
        $this->stock_qty   = isset( $data['stock_qty'] ) ? (int) $data['stock_qty'] : 0;
        $this->image_url   = $data['image_url'] ?? null;

        // Gallery handle
        if ( isset( $data['gallery_ids'] ) ) {
            $this->gallery_ids = is_string( $data['gallery_ids'] )
                ? explode( ',', $data['gallery_ids'] )
                : (array) $data['gallery_ids'];
            $this->gallery_ids = array_filter( array_map( 'intval', $this->gallery_ids ) );
        }

        $this->upsell_ids  = $data['upsell_ids'] ?? null;
        $this->cross_sell_ids = $data['cross_sell_ids'] ?? null;
        $this->created_at  = $data['created_at'] ?? null;
        $this->sales_count = isset( $data['sales_count'] ) ? (int) $data['sales_count'] : 0;
        $this->updated_at  = $data['updated_at'] ?? null;
    }

    public function to_array(): array {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'type'        => $this->type,
            'status'      => $this->status,
            'price'       => $this->price,
            'sale_price'  => $this->sale_price,
            'sku'         => $this->sku,
            'stock_qty'   => $this->stock_qty,
            'image_url'   => $this->image_url,
            'gallery_ids' => $this->gallery_ids,
            'sales_count' => $this->sales_count,
            'upsell_ids'  => $this->upsell_ids,
            'cross_sell_ids' => $this->cross_sell_ids,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'variations'  => array_map( fn($v) => $v->to_array(), $this->variations ),
        ];
    }
}
