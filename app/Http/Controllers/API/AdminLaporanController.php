<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminLaporanController extends Controller
{
    use ApiResponse;

    // Mengecek apakah user adalah admin
    private function isAdmin($user)
    {
        return $user && $user->role === 'admin';
    }

    // Menampilkan seluruh laporan dari semua user
    public function index(Request $request)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak. Anda bukan Admin.', 403);
        }

        // Mengambil data laporan beserta data user (pelapor)
        $laporans = Laporan::with('user:id,name,email')->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($laporans, 'Berhasil mengambil semua data laporan');
    }

    // Memperbarui status laporan
    public function updateStatus(Request $request, $id)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak. Anda bukan Admin.', 403);
        }

        $laporan = Laporan::find($id);

        if (! $laporan) {
            return $this->sendError('Laporan tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:menunggu,diproses,selesai',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', 422, $validator->errors());
        }

        $laporan->status = $request->status;
        $laporan->save();

        return $this->sendResponse($laporan, 'Status laporan berhasil diperbarui');
    }
}
