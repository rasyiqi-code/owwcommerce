<?php
namespace OwwCommerce\Models;

/**
 * Review Model Class
 */
class Review {
    public int $id;
    public int $product_id;
    public ?int $customer_id;
    public int $rating;
    public string $comment;
    public ?string $author_name;
    public ?string $author_email;
    public string $status;
    public string $created_at;

    public function __construct( array $data = [] ) {
        $this->id           = (int) ( $data['id'] ?? 0 );
        $this->product_id   = (int) ( $data['product_id'] ?? 0 );
        $this->customer_id  = isset( $data['customer_id'] ) ? (int) $data['customer_id'] : null;
        $this->rating       = (int) ( $data['rating'] ?? 5 );
        $this->comment      = $data['comment'] ?? '';
        $this->author_name  = $data['author_name'] ?? null;
        $this->author_email = $data['author_email'] ?? null;
        $this->status       = $data['status'] ?? 'approved';
        $this->created_at   = $data['created_at'] ?? '';
    }

    public function to_array(): array {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'customer_id'  => $this->customer_id,
            'rating'       => $this->rating,
            'comment'      => $this->comment,
            'author_name'  => $this->author_name,
            'author_email' => $this->author_email,
            'status'       => $this->status,
            'created_at'   => $this->created_at,
        ];
    }
}
