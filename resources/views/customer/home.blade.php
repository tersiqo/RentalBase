<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $client->nama }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 32px;
            background: #f7f7f7;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .header {
            margin-bottom: 32px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .categories {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .category {
            padding: 8px 14px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .product {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e5e5e5;
        }

        .product h3 {
            margin-top: 0;
        }

        .price {
            font-weight: bold;
            margin-top: 16px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>{{ $client->nama }}</h1>

        <p>
            {{ $client->deskripsi }}
        </p>
    </div>

    <h2>Kategori</h2>

    <div class="categories">
        @foreach ($categories as $category)
            <div class="category">
                {{ $category->nama }}
            </div>
        @endforeach
    </div>

    <h2>Peralatan</h2>

    <div class="products">

        @forelse ($products as $product)

            <div class="product">

                <h3>{{ $product->nama }}</h3>

                <p>
                    {{ $product->deskripsi }}
                </p>

                <p>
                    Kategori:
                    {{ $product->category->nama }}
                </p>

                <p>
                    Stok:
                    {{ $product->stok }}
                </p>

                <div class="price">
                    Rp {{ number_format($product->harga_sewa, 0, ',', '.') }}
                    / hari
                </div>

            </div>

        @empty

            <p>Belum ada peralatan yang tersedia.</p>

        @endforelse

    </div>

</div>

</body>
</html>