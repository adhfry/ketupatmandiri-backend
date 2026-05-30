<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    use ApiResponse;

    // Pastikan hanya admin yang bisa mengakses ini
    private function isAdmin($user)
    {
        return $user && $user->role === 'admin';
    }

    // Mengambil semua data user
    public function index(Request $request)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $users = User::orderBy('created_at', 'desc')->get();
        return $this->sendResponse($users, 'Berhasil mengambil data user');
    }

    // Menambah user baru
    public function store(Request $request)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', 422, $validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return $this->sendResponse($user, 'User berhasil ditambahkan', 201);
    }

    // Mengupdate data user
    public function update(Request $request, $id)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $user = User::find($id);

        if (! $user) {
            return $this->sendError('User tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6', // Password boleh kosong jika tidak diubah
            'role'     => 'required|in:admin,user',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', 422, $validator->errors());
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;

        // Jika password diisi, update passwordnya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return $this->sendResponse($user, 'Data user berhasil diperbarui');
    }

    // Menghapus user
    public function destroy(Request $request, $id)
    {
        if (! $this->isAdmin($request->user())) {
            return $this->sendError('Akses ditolak.', 403);
        }

        $user = User::find($id);

        if (! $user) {
            return $this->sendError('User tidak ditemukan', 404);
        }

        // Opsional: Cegah admin menghapus dirinya sendiri
        if ($request->user()->id == $user->id) {
            return $this->sendError('Anda tidak bisa menghapus akun Anda sendiri.', 403);
        }

        $user->delete();

        return $this->sendResponse(null, 'User berhasil dihapus');
    }
}
