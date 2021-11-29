<?php

$user = $argv[1];
$password = $argv[1];

$url = "https://sinhvien.bachkhoahanoi.edu.vn/";
$urlLogin = $url . "DangNhap/CheckLogin";
$urlProfile = $url . "SinhVien/ThongTinSinhVien";
$ckfile = tempnam("/tmp", "CURLCOOKIE");

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $urlLogin,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => http_build_query(array(
        'UserName' => $user,
        'Password' => $password
    )),
    CURLOPT_COOKIEJAR => $ckfile,
));

// login to get cookie
$curlLogin = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
}
if (isset($error_msg)) {
    echo $error_msg;
} else echo "curl oke \n";

$status = preg_match("/SinhVien/", $curlLogin, $status);

if (!$status) {
    echo $user . "\n";
    echo "Sai user or pass \n--------------\n";
    return;
}

// reset url
curl_setopt($curl, CURLOPT_URL, $urlProfile);

// login to profile
$curlProfile = curl_exec($curl);

$studentProfileMath = array();

$re = '/([Đ|\w].+)<br \/>Đang học[\S\s]*?id="(Ngay_sinh)".+value="(.*)"[\S\s]*?id="(ID_gioi_tinh)"[\S\s]+?selected["|>| >](.+)<\/option>[\S\s]*?value="(.+)" id="(TruongTHPT)"[\S\s]*?value="(.+)" id="(Nam_tot_nghiep)"[\S\s]*?id="(CMND)".+value="(.*)"[\S\s]*?id="(Dien_thoai_NR)".+value="(.*)"[\S\s]*?id="(Dienthoai_canhan)".+value="(.*)"[\S\s]*?id="(Email)".+value="(.*)"[\S\s]*?id="(ID_tinh_tt)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(ID_huyen_tt)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(ID_xa_tt)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(ID_tinh_ns)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(ID_huyen_ns)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(ID_xa_ns)"[\S\s]+?selected">(.+)<\/option>[\S\s]*?id="(Dia_chi_tt)".+value="(.*)"[\S\s]*?id="(Que_quan)".+value="(.*)"[\S\s]*?id="(Dia_chi_bao_tin)".+value="(.*)"[\S\s]*?id="(NoiO_hiennay)".+value="(.*)"[\S\s]*?id="(Ho_ten_cha)".+value="(.*)"[\S\s]*?id="(SDTBo)".+value="(.*)"[\S\s]*?id="(Namsinh_cha)".+value="(.*)"[\S\s]*?id="(Hoat_dong_XH_CT_cha)".+value="(.*)"[\S\s]*?id="(Ho_khau_TT_cha)".+value="(.*)"[\S\s]*?id="(Ho_ten_me)".+value="(.*)"[\S\s]*?id="(SDTMe)".+value="(.*)"[\S\s]*?id="(Namsinh_me)".+value="(.*)"[\S\s]*?id="(Hoat_dong_XH_CT_me)".+value="(.*)"[\S\s]*?id="(Ho_khau_TT_me)".+value="(.*)"[\S\s]*?id="(Ho_ten_nghe_nghiep_anh_em)".+value="(.*)"/';

preg_match($re, $curlProfile, $studentProfileMath);

// reset math
$studentProfileMath[0] = "Ho va ten";

$studenArray = array();

// value dung truoc la index cua value sau
for ($i = 0; $i < count($studentProfileMath) - 1; $i = $i + 2) {

    // 6 vs 8 co value dung truoc
    if ($i == 6 || $i == 8) {
        $studenArray[$studentProfileMath[$i + 1]] = html_entity_decode($studentProfileMath[$i]);
        continue;
    }
    $studenArray[$studentProfileMath[$i]] = html_entity_decode($studentProfileMath[$i + 1]);
}

// write in file
$folderName = $studenArray['Ho va ten'] . "-" . $user;
$studentData = "";
foreach ($studenArray as $key => $value) {
    $studentData .= $key . " => " . $value . "\n";
}

if (!file_exists("./data/" . "$folderName")) {
    mkdir("./data/" . "$folderName", 0777, true);
}

$html = fopen("./data/" . $folderName . "/profile.txt", "w") or die("Unable to open file!");
fwrite($html, $studentData);
fclose($html);

$urlAvatar = array();
preg_match('/src="(.*AnhThe.+.[png|jpg])"/', $curlProfile, $urlAvatar);
$urlAvatar[1] = $url . $urlAvatar[1];

$img = './data/' . $folderName . '/avatar.jpg';
file_put_contents($img, file_get_contents($urlAvatar[1]));

curl_close($curl);
