<?php

namespace App\Traits;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\User;

trait UserHelper
{
    /** USER HELPER */
    public function getCurrentUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return [
                    'status'           => false,
                    'error'            => 'Người dùng không tồn tại.',
                    'error_en'         => 'user_not_found',
                    'http_status_code' => 404
                ];
            }
            return [
                'status'           => true,
                'user'             => $user,
                'http_status_code' => 200
            ];
        } catch (TokenExpiredException $e) {
            return [
                'status'           => false,
                'error'            => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
                'error_en'         => 'token_expired',
                'http_status_code' => $e->getStatusCode()
            ];
        } catch (TokenInvalidException $e) {
            return [
                'status'           => false,
                'error'            => 'Đăng nhập thất bại. Vui lòng đăng nhập lại.',
                'error_en'         => 'token_invalid',
                'http_status_code' => $e->getStatusCode()
            ];
        } catch (JWTException $e) {
            return [
                'status'           => false,
                'error'            => 'Đăng nhập thất bại. Vui lòng đăng nhập lại.',
                'error_en'         => 'token_absent',
                'http_status_code' => $e->getStatusCode()
            ];
        }
    }

    public function getInfoCurrentUser($user)
    {
        switch ($user->dis_or_sup) {
            case 'system':
                $user = User::where([['users.id', $user->id], ['users.active', true], ['positions.active', true], ['files.table_name', 'users']])
                    ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
                    ->leftJoin('files', 'files.table_id', '=', 'users.id')
                    ->select('users.*', 'positions.name as position_name', 'files.path as file_path')
                    ->first();
                break;
            case 'dis':
                $user = User::where([['users.id', $user->id], ['suppliers.active', true], ['distributors.active', true], ['positions.active', true], ['files.table_name', 'users']])
                    ->leftJoin('distributors', 'distributors.id', '=', 'users.dis_or_sup_id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
                    ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
                    ->leftJoin('files', 'files.table_id', '=', 'users.id')
                    ->select('users.*', 'positions.name as position_name', 'files.path as file_path', 'suppliers.name as supplier_name', 'distributors.name as distributor_name')
                    ->first();
                break;
            case 'sup':
                $user = User::where([['users.id', $user->id], ['suppliers.active', true], ['positions.active', true], ['files.table_name', 'users']])
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'users.dis_or_sup_id')
                    ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
                    ->leftJoin('files', 'files.table_id', '=', 'users.id')
                    ->select('users.*', 'positions.name as position_name', 'files.path as file_path', 'suppliers.name as supplier_name')
                    ->first();
                break;
            default:
                break;
        }
        if (!$user)
            return [
                'status'           => false,
                'error'            => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
                'error_en'         => 'user is not exist',
                'http_status_code' => 401
            ];
        return [
            'status' => true,
            'user'   => $user
        ];
    }
}