# PAYUNi for WooCommerce
 * 提供使用WooCommerce購物車模組時，可直接透過安裝設定此套件，以便於快速串接統一金流PAYUNi之金流系統。
# 目錄
 * [版本需求](#版本需求)
 * [安裝方式](#安裝方式)
 * [相關設定](#相關設定)
 
# 版本需求
 <table>
   <tr>
     <th>Wordpress</th>
     <th>WooCommerce</th>
     <th>PHP</th>
   </tr>
   <tr>
     <td align="center">6.0.2</td>
     <td align="center">6.8.2</td>
     <td align="center">8.1.6</td>
   </tr>
 </table>
 
# 安裝方式
  * 將下載下來的壓縮檔直接上傳到外掛模組，流程如下：
    * 購物車後台 → 外掛
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/plugins.jpg" width="10%" height="10%"/><br/><br/>
    * 安裝外掛
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/ins_plugin.jpg" width="30%" height="30%"/><br/>
    * 上傳外掛
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/upload_plugin.jpg" width="30%" height="30%"/><br/>
    * 選擇檔案
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/chose_file.jpg" width="30%" height="30%"/><br/>
    * 選擇「PAYUNi_for_WooCommerce-1.0.zip」 → 立即安裝
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/install_file.jpg" width="30%" height="30%"/><br/>
    * 啟用外掛
    <br/><img src="https://github.com/payuni/sample_picture/blob/main/setup.jpg" width="30%" height="30%"/><br/>
    
# 相關設定
  * 購物車後台 → WooCommerce → 設定 → 付款
  <br/><img src="https://github.com/payuni/sample_picture/blob/main/setting.jpg" width="30%" height="30%"/><br/>
  * 找到「統一金流 PAYUNi」點選管理 
  <br/><img src="https://github.com/payuni/sample_picture/blob/main/setting2.jpg" width="80%" height="80%"/><br/>
  * 整合式支付模組設定
    * 金流設定
      * 請登入PAYUNi平台檢視商店串接資訊取得商店代號 、 Hash Key及 Hash IV。
      * 統一金流 商店代號 ： 填入PAYUNi平台商店的 商店代號
      * 統一金流 Hash Key ： 填入PAYUNi平台商店的 Hash Key
      * 統一金流 IV Key ： 填入PAYUNi平台商店的 IV Key
      * 繳費有效期限(天) ： 設定繳費有效期限
      * 測試模組 ： 是否開啟測試模組
      <br/><img src="https://github.com/payuni/sample_picture/blob/main/setting3.jpg" width="50%" height="50%"/><br/>
