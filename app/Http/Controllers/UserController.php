<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
class UserController extends Controller
{

  public function index(Request $request)
  {
      try {
          $users = User::with(['orders:total_amount,user_id'])->get();
          return UserResource::collection($users);
          return response()->json([
              "data" => $users
          ]);
      } catch (\Throwable $th) {
          return response()->json(['error' => $th->getMessage()], 500);
      }
  }
  public function store(StoreUserRequest $request)
  {
    try {
      $request->validate(
        [
          'name' => 'required',
          'email' => 'required|email|unique:users,email',
          'password' => 'required|min:6',
          'confirm_password' => 'required|min:6',
        ],
        [
          'email.unique' => 'Email already used!',
          'email.required' => "Email is required",
          'email.unique' => "The following email exists",
          'email.email' => "The following email is not valid",
          'password.required' => 'Password is required',
          'confirm_password.required' => 'Confirm password is required',
          'password.min' => 'Password cannot be less than one',
          'confirm_password.min' => 'Confirm password cannot be less than one',
        ]
      );
    //   DB::transaction(function(){

    //   }); unique operation
      DB::beginTransaction(); // partial
      $user = User::create([
        "name" => $request->name,
        "email" => $request->email,
        "password" => bcrypt($request->password),
      ]);

      DB::commit();
      return response()->json(['result' => true, 'user' => $user], 201);
    } catch (\Exception $th) {
      DB::rollBack();
      return response()->json($th->getMessage(), 500);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */

  public function update(Request $request, $id)
  {
    $request->validate(
      [
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . Auth::user()->id,
      ],
      ['name.required' => 'The name is required']

    );
    try {
      DB::beginTransaction();
      $user = User::find(Auth::user()->id);
      if ($request->hasFile('profile')) {
        $file = $request->file('profile');

      }
      $user->update(['name' => $request->name, 'email' => $request->email]);
      DB::commit();
      return response()->json($user, 201);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json($e->getMessage(), 500);
    }
  }


  public function editUser(Request $request)
  {
    $request->validate(
      [
        'id' => 'required|unique:users,id,' . $request->id,
        'role' => ['required'],
        'permissions' => ['required'],
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . $request->id,
      ],
      [
        'email.unique' => 'Email already used!',
        'role.required' => 'Role required',
        'permissions.required' => 'Permission required',
        'email.required' => "Email required",
        'email.unique' => "The following email exists",
      ]
    );
    try {
      DB::beginTransaction();
      $user = User::find($request->id);
      $user->update([
        "name" => $request->name,
        "email" => $request->email,
      ]);

      $targetUser = User::find($request->id);
      $tokens = $targetUser->tokens;
      foreach ($tokens as $token) {
        $token->delete();
      }
      DB::commit();
      return response()->json($user, 201);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json($e->getMessage(), 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    DB::beginTransaction();
    try {
      $ids = explode(",", $id);
      $result = User::whereIn('id', $ids)->delete();
      DB::commit();
      return response()->json($result, 206);
    } catch (\Exception $e) {
      DB::rollback();
      return response()->json($e->getMessage(), 500);
    }
  }
  public function restore(string $id)
  {
    try {
      $ids = explode(",", $id);
      User::whereIn('id', $ids)->withTrashed()->restore();
      return response()->json(true, 203);
    } catch (\Throwable $th) {
      return response()->json($th->getMessage(), 500);
    }
  }
  public function forceDelete(string $id)
  {
    try {
      $ids = explode(",", $id);
      User::whereIn('id', $ids)->withTrashed()->forceDelete();
      return response()->json(true, 203);
    } catch (\Throwable $th) {
      return response()->json($th->getMessage(), 500);
    }
  }

}
