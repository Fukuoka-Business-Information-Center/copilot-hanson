<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    //管理者権限の定数定義
    const GENERAL = 1;
    const ADMIN = 9;
    const MANAGER = 99;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'auth_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     *  オーバーライドすることで認証メールを日本語化
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * オーバーライドすることでメールを日本語化
     * パスワードリセット通知をユーザーに送信
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * 代表者アカウントを取得する
     * @return int 代表のuserID(一番数値が小さいID)
     */
    public static function getOwnerUserID($company_id)
    {
        return User::select(
            'id'
        )->where('company_id', $company_id)
            ->orderBy('id')
            ->get()->first()['id'];
    }

    /**
     * LINE連携しているユーザリスト取得
     * おすすめ案件通知用
     * Try it out: 
     * `/translate SQL #getLINEUserList`
     * `/translate SQLAlchemy #getLINEUserList`
     */
    public static function getLINEUserList()
    {
        return User::select(
            'users.id',
            'users.name',
            'users.line_id',
            'users.company_id',
            DB::raw('GROUP_CONCAT(DISTINCT project_type.project_type_id) as project_type_ids'),     // 対応している工事種類idをカンマ区切りで取得
            DB::raw('GROUP_CONCAT(DISTINCT company_service_area.pref_id) as company_service_area'), // 対応可能地域(県id)をカンマ区切りで取得
        )
            ->join('company', 'users.company_id', '=', 'company.company_id')
            ->leftjoin('company_industry', 'company.company_id', '=', 'company_industry.company_id')
            ->leftjoin('company_service_area', 'company.company_id', '=', 'company_service_area.company_id')
            ->leftjoin('project_type', 'company_industry.industry_id', '=', 'project_type.industry_id')
            ->where('users.line_id', '!=', '')
            ->groupBy(
                'users.id',
                'users.name',
                'users.line_id',
                'users.company_id',
            )
            ->orderBy('users.id')
            ->get();
    }

    /**
     * Route notifications for the FCM channel.
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        // FCMトークンを返す
        return $this->fcm_token;
    }
}
