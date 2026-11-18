<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        // TODO 3: Lengkapi logika index():
        // 1. Buat query pencarian berdasarkan judul, penulis, atau penerbit jika ada input 'search'
        // 2. Tambahkan filter penerbit dan filter ketersediaan stok jika ada input 'penerbit' / 'status'
        // 3. Tambahkan pengurutan data berdasarkan input 'sort' (judul, tahun, stok, default: latest)
        // 4. Lakukan paginasi 8 data dengan withQueryString()
        // 5. Ambil list penerbit unik untuk opsi dropdown filter
        // 6. Return view 'home' dengan data buku dan list penerbit

        $search   = $request->input('search');
        $penerbit = $request->input('penerbit');
        $status   = $request->input('status');
        $sort     = $request->input('sort');

        $query = Buku::query()
            ->when($search, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('judul', 'like', "%{$search}%")
                      ->orWhere('penulis', 'like', "%{$search}%")
                      ->orWhere('penerbit', 'like', "%{$search}%");
                });
            })
            ->when($penerbit, fn ($q, $penerbit) => $q->where('penerbit', $penerbit))
            ->when($status === 'tersedia', fn ($q) => $q->where('stok', '>', 0))
            ->when($status === 'habis', fn ($q) => $q->where('stok', '<=', 0));

        match ($sort) {
            'judul' => $query->orderBy('judul'),
            'tahun' => $query->orderByDesc('tahun_terbit'),
            'stok'  => $query->orderByDesc('stok'),
            default => $query->latest(),
        };

        $buku = $query->paginate(8)->withQueryString();

        $listPenerbit = Buku::select('penerbit')
            ->whereNotNull('penerbit')
            ->distinct()
            ->orderBy('penerbit')
            ->pluck('penerbit');

        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Kembalikan view 'home_show' membawa data $buku
        return view('home_show', compact('buku'));
    }
}
