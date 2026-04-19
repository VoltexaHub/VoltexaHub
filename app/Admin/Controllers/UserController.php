<?php
namespace App\Admin\Controllers;
use Illuminate\Http\Request;
class UserController
{
    public function index() { abort(501); }
    public function show($id) { abort(501); }
    public function edit($id) { abort(501); }
    public function update(Request $request, $id) { abort(501); }
    public function destroy($id) { abort(501); }
    public function ban(Request $request, $id) { abort(501); }
    public function unban(Request $request, $id) { abort(501); }
}
