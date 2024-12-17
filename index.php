<!-- 在消息内容输入框前添加图片上传区域 -->
<div class="mb-4">
  <label for="imageUpload" class="form-label">
    上传图片
    <span class="text-sm text-gray-500 ml-2">可选，最大 5MB</span>
  </label>
  <div class="flex items-center justify-center w-full">
    <label for="imageUpload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
      <div class="flex flex-col items-center justify-center pt-5 pb-6" id="uploadText">
        <i class="fas fa-cloud-upload-alt text-gray-500 text-3xl mb-2"></i>
        <p class="mb-2 text-sm text-gray-500">点击或拖拽图片到这里上传</p>
        <p class="text-xs text-gray-500">支持 PNG, JPG, GIF 格式</p>
      </div>
      <div id="imagePreview" class="hidden w-full h-full">
        <img src="" alt="预览" class="w-full h-full object-contain">
      </div>
      <input id="imageUpload" name="image" type="file" class="hidden" accept="image/*" />
    </label>
  </div>
</div> 

<script>
document.getElementById('imageUpload').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB
      alert('图片大小不能超过 5MB');
      this.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      const preview = document.getElementById('imagePreview');
      const uploadText = document.getElementById('uploadText');
      const img = preview.querySelector('img');
      
      img.src = e.target.result;
      preview.classList.remove('hidden');
      uploadText.classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }
});
</script> 

// 在处理 POST 请求的部分添加图片处理逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['confirm'])) {
  // ... 现有代码 ...
  
  if (!empty($_POST['message'])) {
    $content = $_POST['message'];
    
    // 处理图片上传
    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $imageInfo = getimagesize($_FILES['image']['tmp_name']);
      if ($imageInfo !== false) {
        $imageData = base64_encode(file_get_contents($_FILES['image']['tmp_name']));
      }
    }
    
    // 将图片数据添加到消息数据中
    $messageData = [
      'senderNameEncrypted' => $encryptedSenderName,
      'senderNoteEncrypted' => $encryptedSenderNote,
      'senderPasswordHash' => $hashedSenderPassword,
      'messageEncrypted' => $encryptedMessage,
      'imageData' => $imageData ? encrypt($imageData, $randomKey) : null,
      'keyEncryptedWithVerificationCode' => $keyEncryptedWithVerificationCode,
      'keyEncryptedWithSenderPassword' => $keyEncryptedWithSenderPassword,
      'hashedVerificationCode' => $hashedVerificationCode,
      'createdAt' => time(),
      'expirySeconds' => $userExpirySeconds > 0 ? $userExpirySeconds : $expirySeconds
    ];
    
    // ... 其余代码保持不变 ...
  }
}

// 在显示消息内容的部分添加图片显示
if (isset($messageData['imageData'])) {
  $decryptedImage = decrypt($messageData['imageData'], $randomKey);
  echo '<div class="mb-4">';
  echo '<img src="data:image/jpeg;base64,' . $decryptedImage . '" class="max-w-full h-auto rounded-lg shadow-lg">';
  echo '</div>';
}