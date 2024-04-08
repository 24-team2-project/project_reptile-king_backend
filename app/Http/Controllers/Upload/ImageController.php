<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Aws\Laravel\AwsFacade;
use Exception;

class ImageController extends Controller
{
    // 에디터에서 이미지 업로드
    public function uploadImageForEditor(Request $request){
        $validated = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'division' => ['required', 'string'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'msg' => '이미지 요청이 올바르지 않음',
                'error' => $validated->errors()
            ], 400);
        }

        $image = $validated->validated()['image'];
        $division = $validated->validated()['division'];
        
        $url = $this->getImageUrl($image, $division);

        return (gettype($url) != 'string') ? $url : response()->json([ 'msg' => '이미지 업로드 성공', 'url' => $url,], 200);

    }

    // 컨트롤러에서 이미지 업로드
    public function uploadImageForController($images, $division){
        $imageUrls = [];
        $flag = true;
        $errorMsg = '';

        foreach($images as $image){
            $url = $this->getImageUrl($image, $division);
            if(gettype($url) != 'string'){
                $flag = false;
                $errorMsg = $url;
                break;
            }
            array_push($imageUrls, $url);
        }

        return $flag ? $imageUrls : $errorMsg;
    }

    public function deleteImagesForEditor(Request $request){
        $validated = Validator::make($request->all(), [
            'urls' => ['required', 'string'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'msg' => '이미지 요청이 올바르지 않음',
                'error' => $validated->errors()
            ], 400);
        }

        $urls = json_decode($validated->validated()['urls'], true);
        
        if(gettype($urls) != 'array'){
            return response()->json([
                'msg' => 'urls는 배열이어야 함',
                'error' => json_last_error_msg()
            ], 400);
        }

        $result = $this->deleteImages($urls);
        if($result != true){
            return $result;
        } else{
            return response()->json([ 'msg' => '이미지 삭제 성공',], 200);
        }
    }

    public function deleteImages($urls){
        try {
            $s3 = AwsFacade::createClient('s3');

            foreach($urls as $url){
                $key = ltrim(parse_url($url, PHP_URL_PATH), '/');   // url에서 key(이미지 이름) 추출, ltrim(문자열 왼쪽의 공백 제거), parse_url(주소를 구성 요소로 분석)
                $s3->deleteObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $key,
                ]);
            }
            return true;

        } catch (AwsException $e) {
            return response()->json([
                'msg' => '이미지 삭제 실패',
                'error' => $e->getMessage()
            ], 500);
        }catch (Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    // S3에 이미지 업로드
    public function getImageUrl($image, $division){
        try {
            $s3 = AwsFacade::createClient('s3');

            $imageName = uniqid('image_').'.'.$image->getClientOriginalName(); 
            $uploadPath = 'images/'.$division.'/'.$imageName;
    
            $result = $s3->putObject([
                'Bucket' => env('AWS_BUCKET'),                  // 버킷 이름
                'Key' => $uploadPath,                           // 저장할 파일 이름
                'Body' => fopen($image, 'r'),                   //  파일
                'ContentType' => $image->getClientMimeType(),   // 파일 타입
                // 'ACL' => 'public-read',                         // 파일 접근 권한(public-read: 모든 사용자가 읽을 수 있음), 현재 버킷 설정과 중복되어 주석처리
            ]);
    
            return $result['ObjectURL'];

        } catch (AwsException $e) {
            return response()->json([
                'msg' => '이미지 업로드 실패',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

}
