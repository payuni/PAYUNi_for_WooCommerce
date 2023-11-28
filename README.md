# PAYUNi for WooCommerce
<p align="center">
    <img alt="Last Release" src="https://img.shields.io/github/release/payuni/PAYUNi_for_WooCommerce.svg">
</p>

---------------

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
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/plugins.jpg" width="10%" height="10%"/><br/><br/>
    * 安裝外掛
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/ins_plugin.jpg" width="30%" height="30%"/><br/>
    * 上傳外掛
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/upload_plugin.jpg" width="30%" height="30%"/><br/>
    * 選擇檔案
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/chose_file.jpg" width="30%" height="30%"/><br/>
    * 選擇「PAYUNi_for_WooCommerce-1.0.zip」 → 立即安裝
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/install_file.jpg" width="30%" height="30%"/><br/>
    * 啟用外掛
    <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/setup.jpg" width="30%" height="30%"/><br/>

# 相關設定
- 設定路徑
  - `購物車後台` -> `WooCommerce` -> `設定(Settings)` -> `付款`
  <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/setting.jpg" width="30%" height="30%"/><br/>

- 找到「統一金流 PAYUNi」點選管理
  <br/><img src="https://github.com/payuni/sample_picture/raw/main/woocommerce/setting2.jpg" width="80%" height="80%"/><br/>

- 整合式支付模組設定
  - 金流設定
    - 請登入PAYUNi平台檢視商店串接資訊取得商店代號 、 Hash Key及 Hash IV。
    - 統一金流 商店代號 ： 填入PAYUNi平台商店的 商店代號
    - 統一金流 Hash Key ： 填入PAYUNi平台商店的 Hash Key
    - 統一金流 IV Key ： 填入PAYUNi平台商店的 IV Key
    - 繳費有效期限(天) ： 設定繳費有效期限
    - 測試模組 ： 是否開啟測試模組
    - 超商取貨類型 ： C2C(預設)、B2C
      ![setting4](https://github.com/payuni/sample_picture/raw/main/woocommerce/setting4.jpg)

  - 物流設定
    - 您需要至 `運送方式` -> `運送區域`-> `編輯` -> `新增運送方式` ，加入要提供的物流，並可進入個別物流種類中編輯運費、免運以及啟用門檻。
    ![setting5](https://github.com/payuni/sample_picture/raw/main/woocommerce/setting5.jpg)
    ![setting6](https://github.com/payuni/sample_picture/raw/main/woocommerce/setting6.jpg)
    ![setting7](https://github.com/payuni/sample_picture/raw/main/woocommerce/setting7.jpg)

    - 若有特定商品需過濾在結帳時不顯示711或黑貓運送選項，請至`運送方式`->`運送類別`->`新增或修改運送類別`，
      - 運送類別代稱設定
        - `no711`：分到該運送類別之商品，於結帳時`不會`顯示711物流的選項
        - `nocat`：分到該運送類別之商品，於結帳時`不會`顯示黑貓物流的選項
        ![shippingClassSetting](https://github.com/payuni/sample_picture/raw/main/woocommerce/shippingClassSetting.jpg)

      - 結帳時商品`有`分類到no711運送類別中的商品<br/>
        ![no711](https://github.com/payuni/sample_picture/raw/main/woocommerce/no711.jpg)
      - 結帳時商品`無`分類到no711運送類別中的商品<br/>
        ![have711](https://github.com/payuni/sample_picture/raw/main/woocommerce/have711.jpg)

      - `注意事項`
        請於更新`v1.1.5`版時，重新異動運送方式(隨意開關某一種方式即可)，並儲存設定，使過濾的設定可以生效。
        ![reflashSetting](https://github.com/payuni/sample_picture/raw/main/woocommerce/reflashSetting.jpg)