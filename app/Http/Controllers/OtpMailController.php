<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use DateTime;

class OtpMailController extends Controller
{
    public function OtpMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['data' => false]);
        } else {
            $otp_mail = $request->email;
            $random_OTP = random_int(100000, 999999);

            $query = "SELECT otp_mail FROM otp_send WHERE otp_mail = '$otp_mail'";
            $getMail = DB::select($query);

            // เชคว่ามีเมล์อยู่แล้วหรือไม้ ถ้ามีให้ลบข้อมูลเดิม
            if (!empty($getMail)) {
                // dd("test");
                $query = "DELETE FROM otp_send WHERE otp_mail = '$otp_mail'";
                $deleteMail = DB::delete($query);
            }

            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'damo.kiddiarry@gmail.com';                     //SMTP username
            $mail->Password   = 'wzlvrmtgbliwsjrs';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('damo.kiddiarry@gmail.com', 'OTP Kiddiary');
            $mail->addAddress($otp_mail);               //Name is optional


            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'OTP Kiddiary';
            $htmlBody = '';
            $htmlBody .= '<h1>OTP <b>' . $random_OTP . '</b>';
            $htmlBody .= '<p  style="color:red"> OTP มีอายุ 5 นาทีกรุณากรอก OTP ก่อนหมดอายุ</p>';
            // dd($htmlBody);

            $mail->Body    = $htmlBody;
            // $mail->send();
            if ($mail->send()) {
                $created_at = date("Y-m-d h:i:s");
                $query = "INSERT INTO otp_send (otp_no, otp_mail, created_at) VALUES ('$random_OTP', '$otp_mail', '$created_at')";
                $insert = DB::insert($query);
                return response()->json(['data' => 'send Mail Success']);
            }
        }
    }

    public function confirmOtp(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            // 'otp' => 'required|otp',
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['data' => false]);
        } else {
            $otp = $request->otp;
            $email = $request->email;
            $updated_at = date("Y-m-d h:i:s");

            //get email
            $query = "SELECT * FROM otp_send where otp_mail = '$email'";
            $dataConfirm = DB::select($query);


            if (!empty($dataConfirm) && $dataConfirm[0]->otp_no == $otp) {

                $datetime_from_database = new DateTime($dataConfirm[0]->created_at);
                $datetime_now = new DateTime();
                $datetime_now->modify('-5 minutes'); // ลบ 5 นาทีจาก DateTime ปัจจุบัน
                // dd($datetime_now);
                if ($datetime_now <=  $datetime_from_database ){

                    $query = "UPDATE otp_send SET otp_confirm = 'Y', updated_at = '$updated_at'
                        WHERE otp_mail = '$email'";
                    $updateOtp = DB::update($query);
                    return response()->json(['status'=>true,'data' => 'Confirm OTP Success']);
                } else {
                    return response()->json(['data' => 'OTP More Than 5 Minutes']);
                }
            } else {
                return response()->json(['data' => 'OTP Not Match']);
            }
        };
    }
}
