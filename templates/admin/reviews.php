<div class="wrap owwc-admin-wrap">
    <div class="owwc-admin-header">
        <h1 class="wp-heading-inline">Manajemen Ulasan Produk</h1>
        <hr class="wp-header-end">
    </div>

    <div class="owwc-admin-card" style="margin-top: 20px; padding: 0; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff;">
        <div class="owwc-table-responsive">
            <table class="wp-list-table widefat fixed striped table-view-list reviews-table" style="border: none; box-shadow: none;">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-product" style="width: 15%; padding: 15px;">Produk</th>
                        <th scope="col" class="manage-column column-author" style="width: 15%; padding: 15px;">Penulis</th>
                        <th scope="col" class="manage-column column-rating" style="width: 10%; padding: 15px;">Rating</th>
                        <th scope="col" class="manage-column column-comment" style="padding: 15px;">Komentar</th>
                        <th scope="col" class="manage-column column-status" style="width: 10%; padding: 15px;">Status</th>
                        <th scope="col" class="manage-column column-date" style="width: 15%; padding: 15px;">Tanggal</th>
                        <th scope="col" class="manage-column column-actions" style="width: 10%; text-align: right; padding: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="owwc-reviews-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">Memuat ulasan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.owwc-table-responsive { overflow-x: auto; }
.reviews-table thead th { background: #f9fafb; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb; }
.reviews-table td { vertical-align: middle; padding: 15px !important; }
.rating-stars { color: #f59e0b; font-size: 14px; }
.rating-stars .empty { color: #d1d5db; }
.product-title { font-weight: 600; color: #111827; text-decoration: none; }
.product-title:hover { color: #d4af37; }
.author-info { font-size: 13px; }
.author-email { color: #6b7280; font-size: 12px; }
.comment-text { line-height: 1.5; color: #4b5563; }
.btn-delete-review { color: #dc2626; cursor: pointer; border: none; background: none; padding: 5px; border-radius: 4px; transition: 0.2s; }
.btn-delete-review:hover { background: #fee2e2; }
.btn-approve-review { color: #059669; cursor: pointer; border: none; background: none; padding: 5px; border-radius: 4px; transition: 0.2s; }
.btn-approve-review:hover { background: #d1fae5; }
.owwc-status-badge { padding: 4px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.pending-row { background: #fffcf0 !important; }
</style>
