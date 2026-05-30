<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    use ApiResponse;

    // Menampilkan daftar laporan milik user yang sedang login
    public function index(Request $request)
    {
        $laporans = Laporan::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($laporans, 'Berhasil mengambil data laporan');
    }

    // Membuat laporan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', 422, $validator->errors());
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            // Menyimpan foto di folder storage/app/public/laporan
            $fotoPath = $request->file('foto')->store('laporan', 'public');
        }

        $laporan = Laporan::create([
            'user_id'   => $request->user()->id,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'foto'      => $fotoPath,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'status'    => 'menunggu',
        ]);

        return $this->sendResponse($laporan, 'Laporan berhasil dibuat', 201);
    }

    // Melihat detail laporan
    public function show(Request $request, $id)
    {
        $laporan = Laporan::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (! $laporan) {
            return $this->sendError('Laporan tidak ditemukan', 404);
        }

        return $this->sendResponse($laporan, 'Berhasil mengambil detail laporan');
    }

    // Mengupdate laporan (hanya jika statusnya masih 'menunggu')
    public function update(Request $request, $id)
    {
        $laporan = Laporan::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (! $laporan) {
            return $this->sendError('Laporan tidak ditemukan', 404);
        }

        if ($laporan->status !== 'menunggu') {
            return $this->sendError('Laporan yang sudah diproses atau selesai tidak dapat diedit', 403);
        }

        $validator = Validator::make($request->all(), [
            'judul'     => 'sometimes|string|max:255',
            'deskripsi' => 'sometimes|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', 422, $validator->errors());
        }

        if ($request->hasFile('foto')) {
            if ($laporan->foto) {
                Storage::disk('public')->delete($laporan->foto);
            }
            $laporan->foto = $request->file('foto')->store('laporan', 'public');
        }

        $laporan->update($request->only(['judul', 'deskripsi', 'latitude', 'longitude']));

        return $this->sendResponse($laporan, 'Laporan berhasil diupdate');
    }

    // Menghapus laporan (hanya jika statusnya masih 'menunggu')
    public function destroy(Request $request, $id)
    {
        $laporan = Laporan::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (! $laporan) {
            return $this->sendError('Laporan tidak ditemukan', 404);
        }

        if ($laporan->status !== 'menunggu') {
            return $this->sendError('Laporan yang sudah diproses atau selesai tidak dapat dihapus', 403);
        }

        if ($laporan->foto) {
            Storage::disk('public')->delete($laporan->foto);
        }

        $laporan->delete();

        return $this->sendResponse(null, 'Laporan berhasil dihapus');
    }
}
