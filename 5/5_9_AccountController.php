<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MenuController as Menu;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use App\Model\T_History;
use App\Service\Common;
use App\Model\members;
use App\Model\company_mail_infos;
use App\Model\member_profiles;

/*
 Let's Try
 右クリック → Copilot → レビューとコメント
 */

class AccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //非ログイン時リダイレクト処理
    public function __construct(){
        $this->middleware('auth');
    }


    /**
     * 画面表示
     */
    public function index(){

        $authority = Auth::user()->auth_kbn;
        if($authority != 1 && $authority != 99){
            return redirect()->back();
        }

        $query = company_mail_infos::where('company_id', '=', Auth::user()->company_id);
        $info = $query->first();
        if(is_null($info)){
            $mail_exists = false;
        }else{
            $mail_exists = true;
        }

        $menu = Menu::menuExport();

        $user_id = Auth::id();
        $member_info = members::leftjoin('groups', 'members.group_id', '=', 'groups.id')
        ->where('members.id', '=', $user_id)
        ->first();

        return view('account',['info' => $info, 'mail_exists' => $mail_exists, 'menu' => $menu, 'member_info' => $member_info]);
    }

    //新規登録and更新
    public function upsertAccount(Request $request){
        //フォームデータを取得
        $params = $request->all();
        // 現在のアカウント設定を取得
        $now_config = company_mail_infos::where('company_id','=',Auth::user()->company_id)
        ->first();
        //新規パスワードを暗号化
        $pass = Common::encrypt($params["new_password"],false);
        // エラーメッセージを記録する配列
        $error = array();

        //エラーチェック
        if($now_config){
            //更新用チェック
            $error = $this->updateErrorCheck($params,$now_config);
        }else{
            //新規作成用チェック
            $error = $this->createErrorCheck($params);
        }

        //エラーがあったらリターン
        if($error != null){
            $error_message = "";
            foreach($error as $error){
                $error_message = $error_message.$error.'<br>';
            }
            return redirect()->back()->with('error', $error_message);
        }

        //新規作成または更新
        if($now_config){
            //更新
            $error[] = $this->accountUpdate($params,$pass);
        }else{
            //新規作成
            $error[] = $this->accountCreate($params,$pass);
        }
 
        if($error[0] === null){
            return redirect()->back()->with('message', '更新完了しました');
        }else{
            $error_message = "";
            foreach($error as $error){
                $error_message = $error_message.$error.'<br>';
            }
            return redirect()->back()->with('error', $error_message);
        }
    }

    public function createErrorCheck($params){
        $error = array();

        if(empty($params['mail_address'])){
            $error[] = 'メールアドレスが入力されていません';
        }
        if(empty($params['new_password'])){
            $error[] = '変更パスワードが入力されていません';
        }
        if(strcmp($params['new_password'],$params['confirm_password']) !== 0){
            $error[] = '確認パスワードと違います';
        }
        if(empty($params['server'])){
            $error[] = 'サーバー名が入力されていません';
        }
        if(empty($params['port'])){
            $error[] = 'ポート番号が入力されていません';
        }

        return $error;
    }

    public function updateErrorCheck($params,$now_config){
        $error = array();

        if(empty($params['mail_address'])){
            $error[] = 'メールアドレスが入力されていません';
        }

        //管理者アカウント情報が得られた場合($now_configが存在)
        $check = Common::encrypt($params["old_password"],false);
        if(strcmp($check, $now_config['ps']) != 0){
            $error[] = '現在パスワードが違います';
        } 

        //変更パスワードがあったときのみ確認パスワードと比較する
        if(!empty($params['new_password'])){
            if(strcmp($params['new_password'],$params['confirm_password']) !== 0){
                $error[] = '確認パスワードと違います';
            }
        }

        if(strcmp($params['new_password'],$params['confirm_password']) !== 0){
            $error[] = '確認パスワードと違います';
        }

        if(empty($params['server'])){
            $error[] = 'サーバー名が入力されていません';
        }

        if(empty($params['port'])){
            $error[] = 'ポート番号が入力されていません';
        }

        return $error;
    }

    public function accountCreate($params,$pass){
        try{
            company_mail_infos::create([
                'company_id'=>Auth::user()->company_id,
                'mail_addr'=>$params['mail_address'],
                'ps'=>$pass,
                'server_name'=>$params['server'],
                'port_no'=>$params['port']
            ]);
        }catch(\Exception $e){
            return 'エラー：'.$e->getMessage();
        }
    }

    public function accountUpdate($params,$pass){
        try{
            //変更パスワードがあればパスワード変更
            if(!empty($params['new_password'])){
                company_mail_infos::where('company_id','=',Auth::user()->company_id)
                ->update([
                    'mail_addr'=>$params['mail_address'],
                    'ps'=>$pass,
                    'server_name'=>$params['server'],
                    'port_no'=>$params['port']
                ]);
            }else{
                company_mail_infos::where('company_id','=',Auth::user()->company_id)
                ->update([
                    'mail_addr'=>$params['mail_address'],
                    'server_name'=>$params['server'],
                    'port_no'=>$params['port']
                ]);
            }
        }catch(\Exception $e){
            return 'エラー：'.$e->getMessage();
        }
    }
    public function deleteAccount(Request $request){
        try{
            company_mail_infos::where('company_id','=',Auth::user()->company_id)
                ->delete();
                return redirect()->back()->with('message', '削除しました');  
        }catch(\Exception $e){
            return redirect()->back()->with('error', 'エラーが発生しました');
        }
    }
}