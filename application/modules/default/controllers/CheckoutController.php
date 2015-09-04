<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
class CheckoutController extends Zend_Controller_Action {

    var $sessPublisherInfo = null;
    var $sessCompanyInfo = null;
    var $modelPublisher = null;
    var $modelCompany = null;
    var $modelCatalogue = null;
    var $modelBooks = null;

    public function init() {
        /*         * ********* To check for publisher session data ******************** */
        $producturrency = new Admin_Model_DbTable_Countries();

        $storage_publisher = new Zend_Auth_Storage_Session('publisher_type');
        $publisher_data = $storage_publisher->read();

        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();

        $storage = new Zend_Auth_Storage_Session('account_type');
        $userSessData = $storage->read();
        if (!$userSessData || !$company_data) {
            // $this->_redirect('/');
        }
        if ($publisher_data && $publisher_data != null) {
            $this->modelPublisher = new Model_DbTable_Users();
            $this->sessPublisherInfo = $this->modelPublisher->getInfoByUserId($publisher_data->id);

            $this->view->sessPublisherInfo = $this->sessPublisherInfo;
        } elseif ($company_data && $company_data != null) {
            $this->modelCompany = new Model_DbTable_Companies();
            $this->sessCompanyInfo = $this->modelCompany->getInfoByUserId($company_data->id);
            $this->view->sessCompanyInfo = $this->sessCompanyInfo;
        } elseif ($userSessData && $userSessData != null) {
            $this->modelCompanies = new Company_Model_DbTable_Companies();
            $this->sessUserInfo = $this->modelCompanies->getInfoByCompanyId($userSessData->id);
            $this->view->sessUserInfo = $this->sessUserInfo;
        }
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $allStored = $this->modelBooks->getAllStores();
        $this->view->allStored = $allStored;
        /*         * *********** To Set The active section and links ****************** */
        $controller = $this->_getParam('controller');
        $action = $this->_getParam('action');
        $title = $this->_getParam('title');
        $this->view->currentController = $controller;
        $this->view->currentAction = $action;
        $this->view->currentTitle = $title;
        $this->view->class_active = 'class="active"';
        /*         * *********** To Set The active section and links ****************** */

        $this->view->headScript()->appendFile($this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/js/banner_and_slider.js');


        /*         * *************************************************************** */
        //$storeId=$this->getRequest()->getCookie('sid');
        //$storeName=$this->getRequest()->getCookie('sname');



        $storeSession = new Zend_Session_Namespace('storeInfo');
        if (!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname)) {
            $this->view->storeId = $storeSession->sid;
            $this->view->storeName = $storeSession->sname;
        } else {
            //$modelCountry = new Admin_Model_DbTable_Countries();
            //$country_info = $modelCountry->fetchRow('country="Nigeria"');
            //$this->_redirect('/auth/storechange/storeid/'.$country_info->id);
            //$this->_redirect('/auth/storechange/');
        }
    }

    public function indexAction() {
        
    }

    public function cartAction() {
        $tempObj = new Model_DbTable_Checkout();
        $formdata = $this->getRequest()->getPost();


        $creditHistoryObj = new User_Model_DbTable_Chistory();

        $UsersubObj = new Model_DbTable_Usersubscription();

        $product_details = new Publisher_Model_DbTable_Books();

        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();
        $storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();
        $subscription_price = 0;


        if ($data->id) {
            $user_id = $data->id;
        } elseif ($company_data->id) {
            $user_id = $company_data->id;
        }

        if ($company_data->account_type == '1') {
            $groupObj = new Company_Model_DbTable_Groups();
            $groupList = $groupObj->getGroupList($company_data->id);
        }
        if (count($groupList) > 0 && $_POST['subscription_type'] == '') {

            $group_id = $groupList[0]['id'];
        }
        /* else {

          $group_id = 0;
          //$price_to_show = $subscription_data[0]->individual_price;
          } */
        $group_id = 0;
        $sessionid = session_id();
        if ($this->getRequest()->isPost()) {
            $product_id = $formdata['product_id'];
            //$user_id = $formdata['user_id'];
            $user_type = $formdata['user_type'];
            $store_id = $formdata['store_id'];

            $getallData = $tempObj->fetchAll("session_id='" . $sessionid . "' and product_id='" . $product_id . "' and user_id='" . $user_id . "'");



            $quntity = $formdata['quantity'];
            if ($_POST['chk_button'] == 'Subscribe') {
                $subscription_type = $_POST['subscription_type'];

                $subscription_data = $modelSubscription->fetchAll('id="' . $subscription_type . '"');
            } else {
                $subscription_type = "";
                $subscription_data = "";
            }
            if ($company_data->account_type == '1' && count($subscription_data) > 0) {
                $subscription_price = $subscription_data[0]->group_price_sub;
                $subscription_name = $subscription_data[0]->subscription_type;
                $subscription_store = $subscription_data[0]->country_sub;
                $subscription_language = $subscription_data[0]->language_sub;
                $subscription_issues = $subscription_data[0]->number_of_issues;
                $company_id = $user_id;
            }


            if ($company_data->account_type != '1' && count($subscription_data) > 0) {
                $subscription_price = $subscription_data[0]->individual_price;
                $subscription_name = $subscription_data[0]->subscription_type;
                $subscription_store = $subscription_data[0]->country_sub;
                $subscription_language = $subscription_data[0]->language_sub;
                $subscription_issues = $subscription_data[0]->number_of_issues;
                $company_id = $user_id;
            }

            //$purchasedBook = $creditHistoryObj->fetchAll("bookid ='".$product_id."' and userid='".$user_id."' and payment_status=1");

            $data_product = $product_details->fetchAll('id="' . $product_id . '"');

            if ($data_product[0]['cat_id'] == '3') {
                $purchasedBook = $UsersubObj->fetchAll("product_id ='" . $product_id . "' and user_id='" . $user_id . "'");
            }
            if ($data_product[0]['cat_id'] != '3' && count($subscription_data) == 0) {
                $purchasedBook = $UsersubObj->fetchAll("product_id ='" . $product_id . "' and user_id='" . $user_id . "'");
            }


            /* else if($data_product[0]['cat_id']!='3' && count($subscription_data)>0)
              {
              $purchasedBook = $UsersubObj->fetchAll("product_id ='".$product_id."' and user_id='".$user_id."' and subscription_type='".$_POST['subscription_type']."'");
              } */

            if (count($purchasedBook) > 0) {
                $is_purchase = 1;
            } else {
                $is_purchase = 0;
            }

            /* if($group_id!='0' && $formdata['subscription_type']=='')
              {
              $this->modelCompany = new Company_Model_DbTable_Companies();
              $listGroupMember = $this->modelCompany->getMemberByGroupId($group_id);
              $quntity = count($listGroupMember);
              } */
            if ($quntity == '' && $company_data->account_type != '1') {
                $quntity = '1';
            } else {
                $quntity = '0';
            }



            if (count($getallData) == 0) {
                $data_array = array("product_id" => $product_id, "store_id" => $store_id, "user_id" => $user_id, "user_type" => $user_type, "session_id" => $sessionid, "quntity" => $quntity, "group_id" => $group_id, "is_purchase" => $is_purchase, "subscription_type" => $subscription_type, "subscription_name" => $subscription_name, "subscription_price" => $subscription_price, "subscription_store" => $subscription_store, "subscription_language" => $subscription_language, "subscription_issues" => $subscription_issues, "company_id" => $company_id);

                $tempObj->insert($data_array);
            } else {
                $data_array = array("subscription_type" => $subscription_type, "subscription_price" => $subscription_price, "quntity" => $quntity, "subscription_store" => $subscription_store, "subscription_language" => $subscription_language, "subscription_name" => $subscription_name, "subscription_issues" => $subscription_issues);
                $where = "session_id='" . $sessionid . "' and product_id='" . $product_id . "'";
                $tempObj->update($data_array, $where);
            }
        }
        $this->view->sesid = $sessionid;
    }

    public function cartajaxAction() {
        //$this->view->messages = $this->_flashMessenger->getMessages();

        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();

        $storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();


        if ($data->id) {
            $user_id = $data->id;
        } elseif ($company_data->id) {
            $user_id = $company_data->id;
        }

        $this->_helper->layout->disableLayout();
        $producturrency = new Admin_Model_DbTable_Countries();
        $tempObj = new Model_DbTable_Checkout();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $modelLanguage = new Admin_Model_DbTable_Languages();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();

        $formdata = $this->getRequest()->getPost();

        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();

        $UsersubObj = new Model_DbTable_Usersubscription();

        $sessionid = $formdata['sessionid'];
        $id = $formdata['id'];
        $action_type = $formdata['action'];
        $quantity = $formdata['quantity'];
        $grId = $formdata['grId'];
        if ($company_data->account_type == '1') {
            $groupObj = new Company_Model_DbTable_Groups();
            $groupList = $groupObj->getGroupList($company_data->id);
        }



        if ($action_type == 'delete' && $id != '') {
            $tempObj->delete("id='" . $id . "'");
        }
        if ($action_type == 'increasedecrease' && $id != '' && $quantity != '') {
            $array_data = array("quntity" => $quantity);
            $tempObj->update($array_data, "id='" . $id . "'");
        }
        if ($action_type == 'includegroupmember' && $id != '' && $grId > 0) {

            $tempdatafull = $tempObj->fetchAll('id="' . $id . '"');

            $this->modelCompany = new Company_Model_DbTable_Companies();
            $listGroupMember = $this->modelCompany->getMemberByGroupId($grId);
            $quantity = count($listGroupMember);

            /* if($tempdatafull[0]['subscription_type']==0 || $tempdatafull[0]['subscription_type']=="")
              {
              $array_data = array("quntity"=>$quantity,"group_id"=>$grId);
              $tempObj->update($array_data,"id='".$id."'");
              } */

            /* else
              {
              $array_data = array("group_id"=>$grId);
              } */



            $this->modelCompany = new Company_Model_DbTable_Companies();
            $listGroupMember = $this->modelCompany->getMemberByGroupId($grId);
            $allgroupmember = '<div class="subscribtion">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			<th align="left" valign="middle" colspan="2">Name</th>
			<th align="center" valign="middle">Validity of current subscription / End Date</th>
			</tr>';
            /* $allgroupmember .=  '<tr><th colspan="2" align="left"><ul class="button">
              <li><input type="checkbox" name="all" id="all" onclick="checkedAll();"/></li>

              </ul></th></tr>'; */

            foreach ($listGroupMember as $rowgroupmember) {
                $checkusersub = $UsersubObj->fetchAll('user_id= "' . $rowgroupmember['id'] . '" and product_id = "' . $tempdatafull[0]['product_id'] . '" and group_id="' . $_POST['grId'] . '" and date(end_date)>"' . date('Y-m-d') . '" and subscription_type>0');

                if (count($checkusersub) > 0) {
                    $subscription_name = $checkusersub[0]['subscription_name'] . " / " . $checkusersub[0]['end_date'];
                } else {
                    $subscription_name = 'N/A';
                }
                $explode_users = explode(",", $tempdatafull[0]['group_user_selected']);

                if (in_array($rowgroupmember['id'], $explode_users)) {
                    $checkeddata = 'checked';
                } else {
                    $checkeddata = '';
                }



                $allgroupmember .= '<tr>
			<td align="left" valign="middle"><input type="checkbox" name="groupmember[]" id="groupmember[]" value=' . $rowgroupmember['id'] . ' class="chk" ' . $checkeddata . '/>';


                $allgroupmember .= '</td><td align="left" valign="middle">' . $rowgroupmember['first_name'] . " " . $rowgroupmember['last_name'] . '</td>
			<td align="center" valign="middle">' . $subscription_name . '</td></tr>';
            }
            /* $allgroupmember .=  '<tr><th colspan="2" align="left"><ul class="button">
              <li><input type="checkbox" name="all" id="all" onclick="checkedAll();"/></li> */

            //$allgroupmember .=  '</ul></th></tr>';
            $allgroupmember .= '</table></div>';
        }


        $getallData1 = $tempObj->fetchAll("session_id='" . $sessionid . "' and user_id='" . $user_id . "'");
        $counter_subscription = 0;
        foreach ($getallData1 as $chkdata) {
            $array_user_id[] = $chkdata['user_id'];
            if ($chkdata['subscription_type'] != '') {
                $counter_subscription++;
                break;
            }
        }


        if ($action_type == 'storememberdata') {
            $explode_selusers = explode(",", $_POST['selectedusers']);
            if ($_POST['selectedusers'] != '') {
                for ($j = 0; $j < count($explode_selusers); $j++) {
                    $explode_selusers = explode(",", $_POST['selectedusers']);
                    $array_data = array("quntity" => count($explode_selusers), "group_id" => $_POST['grId'], "group_user_selected" => $_POST['selectedusers']);
                    $tempObj->update($array_data, "id='" . $id . "'");
                }
            } else {
                $array_data = array("quntity" => 0, "group_id" => $_POST['grId']);
                $tempObj->update($array_data, "id='" . $id . "'");
            }
        }


        if ($grId > 0 && $_POST['subscription_type'] == '' && $action_type != 'storememberdata') {
            $this->modelCompany = new Company_Model_DbTable_Companies();
            $listGroupMember = $this->modelCompany->getMemberByGroupId($grId);
            $quantity1 = count($listGroupMember);
            $array_data1 = array("quntity" => $quantity1, "group_id" => $grId);
            //echo $temdataHistory->product_id;
            $wherecond = "session_id='" . $sessionid . "' and user_id='" . $user_id . "' and group_id = '" . $grId . "'";


            $tempObj->update($array_data1, $wherecond);
        }





        //$this->view->tempOrderData = $getallData;
        $datahistory = '';
        $datahistory = "<div class='top_heading'>
		    	<div class='item'>Item(s)</div>";
        if ($counter_subscription > 0) {
            $datahistory .= "<div class='prices'>Subscription Type</div>";
        }
        $datahistory .= "<div class='prices'>Price</div>
							<div class='quantity'>";
        if ($company_data->account_type == '1') {
            $datahistory.="QTY";
        } else {
            $datahistory.="&nbsp;";
        }
        $datahistory.="</div>";
        $datahistory.="<div class='total'>Total</div>
			</div>";
        $total_price = '';

        $getallData = $tempObj->fetchAll("session_id='" . $sessionid . "' and user_id='" . $user_id . "'");

        if (count($getallData) > 0) {
            foreach ($getallData as $temdataHistory) {

                $productPriceInfo = $productPrice->getPriceByStoreId($temdataHistory->product_id, $temdataHistory->store_id);

                $subscription_data = $modelSubscription->fetchAll('id="' . $temdataHistory->subscription_type . '"');

                if ($company_data->account_type == '1') {
                    $productPriceInfo['price'] = $productPriceInfo['group_price'];
                } else {
                    $productPriceInfo['price'] = $productPriceInfo['price'];
                }
                $product_details = $this->modelBooks->fetchRow("id='" . $temdataHistory->product_id . "'");
                $catInfo = $this->modelBooks->getCategoryInfo($product_details['cat_id']);
                $product_details['title'];
                $publisherInfo = $modelAuthor->getInfoByPublisherId($product_details['publisher_id']);
                $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
                if (!empty($getBrandInfo) && is_numeric($product_details['title'])) {
                    $titleBrand = $getBrandInfo['brand'];
                } else {
                    $titleBrand = $product_details['title'];
                }

                if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {

                    $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                    $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                    if (!empty($getParentBrandInfo)) {
                        //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                        //$titleBrand=$getParentBrandInfo['brand']." - <em class='brndtitle'>".$titleBrand."</em>";

                        $titleBrand = $getParentBrandInfo['brand'];
                    }
                }

                if ($product_details['cat_id'] == '3') {

                    $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                    $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                    if (!empty($getParentBrandInfo)) {
                        //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                        $titleBrand = $getParentBrandInfo['brand'] . " - <em class='brndtitle'>" . $titleBrand . "</em>";
                    }
                }


                $getCurrencyName = $producturrency->getCurrencyCode($temdataHistory->store_id);
                $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
                $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
                //$langDetail=$modelLanguage->fetchRow('id="'.$product_details['language_id'].'"');
                //$storeDetail=$modelStore->fetchRow('id="'.$product_details['country_id'].'"');
                $titleBrand = $titleBrand;
                $datahistory.='<div class="cart_detail">
		    	<div class="item_content">
		   	    	<img src="' . $this->view->serverUrl() . $this->view->baseUrl() . "/" . USER_UPLOAD_DIR . 'thumb1_' . $imageInfo['image_name'] . '" width="140" height="175" alt="">
		            <h5>' . stripslashes($titleBrand) . '</h5>';
                if ($authorInfo['first_name'] != '') {
                    $datahistory.='<span><em>by: </em> ' . stripslashes($authorInfo['first_name'] . '&nbsp;' . $authorInfo['last_name']) . '</span>';
                }
                $datahistory.='<span><em>Category: </em> ' . stripslashes($catInfo['category_name']) . '</span>
		            <span><em>Publisher</em>: ' . $publisherInfo['publisher'] . '</span>
		            <div class="remove_bt"><a href="javascript:void(0);" onclick=removeProduct("' . $temdataHistory->id . '","' . $sessionid . '");><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/remove_botton.png" width="90" height="25" alt=""></a></div>
		        </div>
		        <div class="space_content">';
                if (count($groupList) > 0) {
                    $drop = "<select name='group_id' onchange=getGroupmem(this.value,'" . $temdataHistory->id . "','" . $sessionid . "');>";


                    $drop .= '<option value="">Select</option>';

                    if ($temdataHistory->group_id == '0') {

                        //$drop.="<option value='0' selected >individual</option>";
                    } else {
                        //$drop.="<option value='0' >individual</option>";
                    }

                    foreach ($groupList as $group) {
                        if ($group->id == $temdataHistory->group_id) {
                            $drop.="<option value='" . $group->id . "' selected>" . $group->group_name . "</option>";
                        } else {
                            $drop.="<option value='" . $group->id . "'>" . $group->group_name . "</option>";
                        }
                    }
                    $drop.="</select>";
                }
                if (count($groupList) > 0) {
                    /* $drop.="<script type='text/javascript'>getGroupmem('".$groupList[0]['id']."','".$temdataHistory->id."','".$sessionid."');</script>"; */
                }
                $datahistory.= $drop;
                $datahistory.='</div>';
                if (count($subscription_data) > 0) {
                    /* if($company_data->account_type == '1')
                      {
                      $price_to_show = $subscription_data[0]->group_price_sub;
                      }
                      else
                      {
                      $price_to_show = $subscription_data[0]->individual_price;
                      } */

                    $price_to_show = $temdataHistory->subscription_price;
                    $datahistory .= '<div class="prices_content">
					' . $temdataHistory->subscription_name . '
				</div>';
                } else {
                    $price_to_show = $productPriceInfo['price'];
                    $datahistory .= '<div class="prices_content">
									N/A
				</div>';
                }

                $datahistory .= '<div class="prices_content">
				&#x20a6;' . @number_format($producturrency->currencyconverter($getCurrencyName, "NGN", $price_to_show), 2) . '
				</div>';
                /* if($temdataHistory->is_purchase ==1 && $company_data->account_type != '1')
                  {
                  $datahistory.='<div class="prices_content">You have already purchased.</div>';
                  }
                  else
                  {
                  $datahistory.='<div class="prices_content">&#x20a6;'.@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$price_to_show),2).'</div>';
                  } */




                if ($company_data->account_type == '1') {
                    $datahistory.='
		        	<div class="quantity_count">
		            	' . $temdataHistory->quntity . '
		             </div>
		        ';
                } else {
                    $datahistory.="<div class='without_count'>&nbsp;</div>";
                }




                if ($temdataHistory->is_purchase == 1 && $company_data->account_type != '1') {
                    $datahistory.='<div class="prices_content">You have already purchased.</div>';
                } else {
                    if (count($subscription_data) > 0) {
                        $total_price_value = $price_to_show * $temdataHistory->quntity;
                    } else {
                        $total_price_value = $temdataHistory->quntity * $productPriceInfo['price'];
                    }
                    $datahistory.='<div class="total_content">&#x20a6;' . @number_format($producturrency->currencyconverter($getCurrencyName, "NGN", $total_price_value), 2) . '</div>    
		 		</div>';
                }
                if ($company_data->account_type == '1') {
                    $total_price = $total_price + $producturrency->currencyconverter($getCurrencyName, "NGN", $total_price_value);
                } else {
                    if ($temdataHistory->is_purchase != 1) {
                        $total_price = $total_price + $producturrency->currencyconverter($getCurrencyName, "NGN", $total_price_value);
                    }
                }
            }
        }
        $totalDesc = '<div class="shipping_total">
					<div class="row">
				    	<div class="lt">Total</div>
				        <div class="rt">&#x20a6;' . @number_format($total_price, 2) . '</div>
				    </div>
				    <div class="row" style="border:none;">
				    	<div class="lt">Tax</div>
				        <div class="rt">&#x20a6;' . '00.00</div>
				    </div>
				    
				    <div class="row subtotal">
				    	<div class="lt">Subtotal</div>
				        <div class="rt">&#x20a6;' . @number_format($total_price, 2) . '</div>
				    </div>
					<div class="check_terms">
					<input type="checkbox" name="terms_condition" id="terms_condition" value="1" > I agree to the <a href="' . $this->view->serverUrl() . $this->view->baseUrl() . '/page/index/title/terms-and-conditions" target="_blank">Terms & Conditions</a>
					<div id="msg_alert_terms" style="display:none;" ></div>
					</div>
					<div>
					Payment option
						<select name="paymentOption" id="paymentOption" onchange="getPaymentOp(this.value);" >
						<option value="" >Select One</option>
						<option value="visa" >Visa</option>
						<option value="master" >MasterCard</option>
						</select>
					</div>
				   
				    <div class="row" style="border:none;">
				    <input type="hidden"  name="user_id"  id="user_id"  value="' . $user_id . '" >
				  				    <input type="hidden"  name="total_price"  id="total_price"  value="' . $total_price . '" >
									
				  				    <input type="hidden"  name="sess_id"  id="sess_id"  value="' . $sessionid . '" >';

        if (count($groupList) <= 0 && $company_data->account_type == '1') {
            $totalDesc .='<input type="image" value="image" name="button" onclick="return getNotGroupMessage()"  src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
        } elseif ($total_price == '' || $total_price == 0) {
            $totalDesc .='<input type="image" value="image" name="button" onclick="return getNotPriceMessage()"  src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
        } else {
            $totalDesc .='<input type="image" value="image" name="submit"  src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
        }

        $totalDesc .=' </div>				   
				</div>';






        echo $datahistory . "####" . $totalDesc . "####" . $allgroupmember;
        exit;
    }

    public function procedcheckoutAction() {
        $tempObj = new Model_DbTable_Checkout();
        $producturrency = new Admin_Model_DbTable_Countries();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $groupSubsObj = new Company_Model_DbTable_GroupSubscriptions();
        $UsersubObj = new Model_DbTable_Usersubscription();
        //$price = 1000;


        $subscription_store = "";
        $subscription_language = "";
        $subscription_issues = "";


        $formdata = $this->getRequest()->getPost();
        $price = $formdata['total_price'];
        $sessid = $formdata['sess_id'];
        //	echo "<pre>";
        //print_r($formdata);
        //exit;

        $user_id = $formdata['user_id'];
        if (isset($price) && $price != 0) {
            $price = $price * 100; // multiply the price by 100 because TWPG deals price in kobo.
            $xml = "<?xml version='1.0' encoding='UTF-8'?>
					<TKKPG>
					<Request>
					<Operation>CreateOrder</Operation>
					<Language>EN</Language>
					<Order>
					<Merchant>EVENDOR</Merchant>
					<Amount>" . $price . "</Amount>
					<Currency>566</Currency>
					<Description>Payment for Digital Publication</Description>
					<ApproveURL>" . $this->view->serverUrl() . $this->view->baseUrl() . "/checkout/approved/</ApproveURL>
					<CancelURL>" . $this->view->serverUrl() . $this->view->baseUrl() . "/checkout/declined/</CancelURL>
					<DeclineURL>" . $this->view->serverUrl() . $this->view->baseUrl() . "/checkout/declined/</DeclineURL>
					</Order>
					</Request>
					</TKKPG>";
            $ch = curl_init(); //this is to initialize curl
            curl_setopt($ch, CURLOPT_URL, "https://196.46.20.36:5443/Exec");
            //curl_setopt($ch, CURLOPT_URL,"https://mpi.valucardnigeria.com:5443/Exec"); 

            curl_setopt($ch, CURLOPT_SSLVERSION, '1');
            curl_setopt($ch, CURLOPT_VERBOSE, '1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CAINFO, getcwd() . '/public/paymentgateway/cert/CAcert.crt');
            curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/public/paymentgateway/cert/myshop.pem');
            curl_setopt($ch, CURLOPT_SSLKEY, getcwd() . '/public/paymentgateway/cert/myshop.key');
            //curl_setopt($ch, CURLOPT_SSLKEY, getcwd().'/public/paymentgateway/cert/myshop.key');

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            $response = curl_exec($ch); // this line post to our server	
            //echo "<pre>";
            //print_r($response);
            //exit;
            //echo htmlentities($response); //use this check the response sent by our PG at every point
            //exit;

            if (!(curl_errno($ch) > 0)) {    //$ch>=0 shows error while $ch<=0 shows no error
                $parsedxml = simplexml_load_string($response);

                foreach ($parsedxml->children() as $RESPONSENODE) {
                    foreach ($RESPONSENODE->children() as $ORDERNODE) {
                        foreach ($ORDERNODE->children() as $child) {
                            if ($child->getName() == "OrderID")
                                $orderid = $child;

                            if ($child->getName() == "SessionID")
                                $sessionid = $child;

                            if ($child->getName() == "URL")
                                $url = $child;
                        }
                    }
                }//end all loop

                $gateway_url = $url . "?ORDERID=" . $orderid . "&SESSIONID=" . $sessionid;

                /*
                 *
                  THE ABOVE FORMED URL ($gateway_url) IS THE URL USED TO
                  CALL THE PAYMENT GATEWAY....
                  YOU CAN USE THIS URL IN THE SOURCE (src) OF AN IFRAME.
                  E.G
                  <iframe src= "<?php echo $gateway_url ?>" frameborder="0" scrolling="no"></iframe>
                 *
                 */
                if ($orderid != '' && $sessionid != '') {
                    $array_order_status = array("order_id" => $orderid, "order_sessionid" => $sessionid);
                    $tempObj->update($array_order_status, 'user_id="' . $user_id . '" and session_id="' . $sessid . '"');
                }
                $storage_company = new Zend_Auth_Storage_Session('company_type');
                $company_data = $storage_company->read();
                if ($company_data->account_type == '1') {
                    $tempData = $tempObj->fetchAll("order_id='" . $orderid . "' and session_id='" . $sessid . "'");
                } else {
                    $tempData = $tempObj->fetchAll("order_id='" . $orderid . "' and is_purchase!='1' and session_id='" . $sessid . "'");
                }

                $order_details = "<table cellpadding='1' style='background:#656565;color:#fff;'><tr><td colspan='3'>Order Id:" . $orderId . "</td></tr><tr><td>Item</td><td>Price</td><td>Quantity</td></tr>";
                foreach ($tempData as $dataDet) {
                    $productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'], $dataDet['store_id']);


                    if ($dataDet['group_id'] != '' && $dataDet['group_id'] != 0) {
                        $dateTime = date('Y-m-d H:i');
                        $price = $productPriceInfo['group_price'];
                        $arrayDataGroup = array("publication_id" => $dataDet['product_id'], "group_id" => $dataDet['group_id'], "company_id" => $dataDet['user_id'], "assigned_date" => $dateTime);
                        //$groupSubsObj->insert($arrayDataGroup);
                    } else {
                        $price = $productPriceInfo['price'];
                    }
                    $orderSt = 0;
                    $product_details = $this->modelBooks->fetchRow("id='" . $dataDet['product_id'] . "'");
                    $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
                    if (!empty($getBrandInfo) && is_numeric($product_details['title'])) {
                        $titleBrand = $getBrandInfo['brand'];
                    } else {
                        $titleBrand = $product_details['title'];
                    }
                    if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {
                        $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                        $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                        if (!empty($getParentBrandInfo)) {
                            //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                            //$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
                            $titleBrand = $getParentBrandInfo['brand'];
                        }
                    }
                    $getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);
                    $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
                    $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);

                    if ($dataDet['subscription_type'] == '0' || $dataDet['subscription_type'] == '') {
                        if ($dataDet['group_id'] > 0) {
                            $price = $productPriceInfo['group_price'];
                        } else {
                            $price = $productPriceInfo['price'];
                        }
                    } else {
                        $price = $dataDet['subscription_price'];
                        $subscription_store = $dataDet['subscription_store'];
                        $subscription_language = $dataDet['subscription_language'];
                        $subscription_issues = $dataDet['subscription_issues'];
                    }




                    $converted_price = $producturrency->currencyconverter($getCurrencyName, "NGN", $price);

                    $tempDatInsert = array();

                    $tempDatInsert['userid'] = $dataDet['user_id'];
                    $tempDatInsert['bookid'] = $dataDet['product_id'];
                    $tempDatInsert['store_id'] = $dataDet['store_id'];
                    $tempDatInsert['price'] = $price;
                    $tempDatInsert['quantity'] = $dataDet['quntity'];
                    $tempDatInsert['book_name'] = $titleBrand;
                    $tempDatInsert['add_date'] = date('Y-m-d H:i:s');
                    $tempDatInsert['group_id'] = $dataDet['group_id'];
                    $tempDatInsert['order_id'] = $orderid;
                    $tempDatInsert['payment_status'] = $orderSt;
                    $tempDatInsert['converted_price'] = $converted_price;
                    $tempDatInsert['subscription_type'] = $dataDet['subscription_type'];
                    $tempDatInsert['subscription_name'] = $dataDet['subscription_name'];
                    $tempDatInsert['subscription_store'] = $dataDet['subscription_store'];
                    $tempDatInsert['subscription_language'] = $dataDet['subscription_language'];
                    $tempDatInsert['subscription_issues'] = $dataDet['subscription_issues'];

                    $creditHistoryObj->insert($tempDatInsert);

                    $todaysdate = date('Y-m-d H:i:s');

                    if ($dataDet['subscription_name'] == 'Weekly') {
                        $date = new DateTime($todaysdate);
                        $date->modify("+7 day");
                        $end_date = $date->format("Y-m-d H:i:s");
                    } else if ($dataDet['subscription_name'] == 'Monthly') {
                        $date = new DateTime($todaysdate);
                        $date->modify("+30 day");
                        $end_date = $date->format("Y-m-d H:i:s");
                    } else if ($dataDet['subscription_name'] == 'Quarterly') {
                        $date = new DateTime($todaysdate);
                        $date->modify("+90 day");
                        $end_date = $date->format("Y-m-d H:i:s");
                    } else if ($dataDet['subscription_name'] == 'Half Yearly') {
                        $date = new DateTime($todaysdate);
                        $date->modify("+182 day");
                        $end_date = $date->format("Y-m-d H:i:s");
                    } else if ($dataDet['subscription_name'] == 'Yearly') {
                        $date = new DateTime($todaysdate);
                        $date->modify("+365 day");
                        $end_date = $date->format("Y-m-d H:i:s");
                    }



                    $subscriptionObj = array();
                    if ($company_data->account_type == '1') {
                        $explode_users = explode(",", $dataDet['group_user_selected']);

                        for ($x = 0; $x < count($explode_users); $x++) {

                            $subscriptionObj['order_id'] = $orderid;
                            $subscriptionObj['product_id'] = $dataDet['product_id'];
                            $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                            $subscriptionObj['author_id'] = $product_details['author_id'];
                            $subscriptionObj['group_id'] = $dataDet['group_id'];
                            $subscriptionObj['user_id'] = $explode_users[$x];
                            $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                            $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                            $subscriptionObj['subscription_price'] = $price;
                            $subscriptionObj['country'] = $dataDet['subscription_store'];
                            $subscriptionObj['language'] = $dataDet['subscription_language'];
                            $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                            $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                            $subscriptionObj['end_date'] = $end_date;
                            $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                            $subscriptionObj['publication_id'] = $product_details['parent_brand_id'];
                            $subscriptionObj['company_id'] = $dataDet['company_id'];

                            if ($dataDet['subscription_type'] == 0) {
                                $checksubscription = $UsersubObj->fetchAll('product_id="' . $dataDet['product_id'] . '" and user_id="' . $explode_users[$x] . '" and order_id="'.$orderid.'"');
                            }
                            if ($dataDet['subscription_type'] > 0) {
                                $checksubscription = $UsersubObj->fetchAll('user_id="' . $explode_users[$x] . '" and publication_id="' . $product_details['parent_brand_id'] . '" and order_id="'.$orderid.'"');
                            }
                            if (count($checksubscription) == 0) {
                                $UsersubObj->insert($subscriptionObj);
                            } else {
                                $totalissues_remaining = $checksubscription[0]['remaining_downloads'] + $dataDet['subscription_issues'];

                                $subscriptionObj['product_id'] = $dataDet['product_id'];
                                $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                                $subscriptionObj['author_id'] = $product_details['author_id'];

                                $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                                $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                                $subscriptionObj['subscription_price'] = $price;
                                $subscriptionObj['country'] = $dataDet['subscription_store'];
                                $subscriptionObj['language'] = $dataDet['subscription_language'];
                                $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                                $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                                $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                                $subscriptionObj['end_date'] = $end_date;

                                $condition = 'user_id=' . $explode_users[$x] . ' and publication_id=' . $product_details['parent_brand_id'] . ' and order_id="'.$orderid.'"';

                                $UsersubObj->update($subscriptionObj, $condition);
                            }
                        }
                    } else {
                        $subscriptionObj['order_id'] = $orderid;
                        $subscriptionObj['product_id'] = $dataDet['product_id'];
                        $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                        $subscriptionObj['author_id'] = $product_details['author_id'];
                        $subscriptionObj['group_id'] = $dataDet['group_id'];
                        $subscriptionObj['user_id'] = $dataDet['user_id'];
                        $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                        $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                        $subscriptionObj['subscription_price'] = $price;
                        $subscriptionObj['country'] = $dataDet['subscription_store'];
                        $subscriptionObj['language'] = $dataDet['subscription_language'];
                        $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                        $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                        $subscriptionObj['end_date'] = $end_date;
                        $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                        $subscriptionObj['publication_id'] = $product_details['parent_brand_id'];



                        if ($dataDet['subscription_type'] == 0) {
                            $checksubscription = $UsersubObj->fetchAll('product_id="' . $dataDet['product_id'] . '" and user_id="' . $dataDet['user_id'] . '" and order_id="'.$orderid.'"');
                        }
                        if ($dataDet['subscription_type'] > 0) {
                            $checksubscription = $UsersubObj->fetchAll('user_id="' . $dataDet['user_id'] . '" and publication_id="' . $product_details['parent_brand_id'] . '" and order_id="'.$orderid.'"');
                        }


                        if (count($checksubscription) == 0) {
                            $UsersubObj->insert($subscriptionObj);
                        } else {
                            $totalissues_remaining = $checksubscription[0]['remaining_downloads'] + $dataDet['subscription_issues'];

                            $subscriptionObj['order_id'] = $orderid;
                            $subscriptionObj['product_id'] = $dataDet['product_id'];
                            $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                            $subscriptionObj['author_id'] = $product_details['author_id'];

                            $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                            $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                            $subscriptionObj['subscription_price'] = $price;
                            $subscriptionObj['country'] = $dataDet['subscription_store'];
                            $subscriptionObj['language'] = $dataDet['subscription_language'];
                            $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                            $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                            $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                            $subscriptionObj['end_date'] = $end_date;

                            $condition = 'user_id=' . $dataDet['user_id'] . ' and publication_id=' . $product_details['parent_brand_id'] . ' and order_id="'.$orderid.'"';




                            $UsersubObj->update($subscriptionObj, $condition);
                        }
                    }
                }

                //echo $gateway_url;
                //exit;
                header("location: " . $gateway_url);
            } else {
                echo curl_error($ch);
                exit;
            }
            exit;
        } else {
            $this->_redirect('/checkout/cart/');
        }
    }

    public function approvedAction() {
        $producturrency = new Admin_Model_DbTable_Countries();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $tempObj = new Model_DbTable_Checkout();
        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $groupSubsObj = new Company_Model_DbTable_GroupSubscriptions();
        $UsersubObj = new Model_DbTable_Usersubscription();
        $userCompanyObj = new Company_Model_DbTable_Companies();

        $response = $_POST['xmlmsg'];
        $xml_string = $_POST['xmlmsg'];
        $parsedxml = simplexml_load_string(stripslashes($response));
        //echo "<pre>";
        //print_r($parsedxml);
        $formDataTransApproved = array();
        foreach ($parsedxml as $RESPONSENODE) {
            //echo $RESPONSENODE."<br/>";
            if ($RESPONSENODE->getName() == 'OrderID') {
                $orderId = $RESPONSENODE;
                $orderIdForPurchase = $RESPONSENODE;
                $orderIdss = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'TransactionType') {
                $transactionType = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'PAN') {
                $pan = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'PurchaseAmountScr') {
                $purchaseAmount = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'TranDateTime') {
                $tranDateTime = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ResponseCode') {
                $responseCode = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ResponseDescription') {
                $responseDescription = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'OrderStatusScr') {
                $orderStatus = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ApprovalCode') {
                $approvalCode = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'MerchantTranID') {
                $merchantTranId = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'OrderDescription') {
                $orderDescription = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ApprovalCodeScr') {
                $approvalCodeScr = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'CurrencyScr') {
                $currency = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ThreeDSVerificaion') {
                $threeDsVerification = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'Brand') {
                $brandname = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'Name') {
                $card_holder_name = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ThreeDSStatus') {
                $ThreeDSStatus = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'MerchantTranID') {
                $MerchantTranID = $RESPONSENODE;
            }
        }
        $formDataTransApproved = array("orderId" => $orderId,
            "transactionType" => $transactionType,
            "pan" => $pan,
            "purchaseAmount" => $purchaseAmount,
            "tranDateTime" => $tranDateTime,
            "responseCode" => $responseCode,
            "responseDescription" => $responseDescription,
            "orderStatus" => $orderStatus,
            "approvalCode" => $approvalCode,
            "approvalCode" => $approvalCode,
            "merchantTranId" => $merchantTranId,
            "orderDescription" => $orderDescription,
            "approvalCodeScr" => $approvalCodeScr,
            "currency" => $currency,
            "brand" => $brandname,
            "card_holder_name" => $card_holder_name,
            "ThreeDSStatus" => $ThreeDSStatus,
            "threeDsVerification" => $threeDsVerification,
            "transaction_xml" => $xml_string);

        $inserted_id = $transactionHistoryObj->insert($formDataTransApproved);

        ##############  update table ###########################
        if ($orderStatus == 'APPROVED') {
            $orderSt = 1;
        } else {
            $orderSt = 0;
        }

        $tempTransactionUpdate = array("payment_status" => $orderSt, "transaction_id" => $inserted_id);
        //$tempObj->update($tempTransactionUpdate,'user_id="'.$user_id.'"');					
        $creditHistoryObj->update($tempTransactionUpdate, "order_id='" . $orderId . "'");
        ##########################################################
        $total_price = '';
        $tempData = $tempObj->fetchAll("order_id='" . $orderId . "' and is_purchase!='1'");
 
        //$tempData = $tempObj->fetchAll("order_id='38115' and is_purchase!='1'");	
        $css1 = ' style="text-align:center;padding:5px;word-wrap:break-word;color:#fff;max-width: 150px;" ';        
        $order_details = "<table cellpadding='4' cellpadding='1' width='100%' style='background:#656565;;color:#fff;border-collapse:collapse;' border='1' BORDERCOLOR='#000'><tr><td colspan='9'>Your reference Id</td></tr><tr><td colspan='9'>Order ID:" . $orderId . "</td></tr><tr><td colspan='9'>Transaction ID:" . $inserted_id . "</td></tr><tr><td".$css1.">Item Image</td><td".$css1.">Title</td><td".$css1.">Subscribed users</td><td".$css1.">Price</td><td".$css1.">Subscription Details</td><td".$css1.">Number of Issues</td><td".$css1.">Start Date</td><td".$css1.">End Date</td><td".$css1.">Quantity</td></tr>";
        $array_new = array();
        $new_product_array = array();

        foreach ($tempData as $dataDet) {
            $publication_det = $this->modelBooks->fetchRow("id='" . $dataDet['product_id'] . "'");
            if (@in_array($publication_det['publisher_id'], $array_new)) {
                $new_product_array[$publication_det['publisher_id']][] = array("id" => $dataDet['id'], "product_id" => $dataDet['product_id'], "user_id" => $dataDet['user_id'], "user_type" => $dataDet['user_type'], "store_id" => $dataDet['store_id'], "quntity" => $dataDet['quntity'], "group_id" => $dataDet['group_id'], "order_id" => $dataDet['group_id']);
            } else {
                array_push($array_new, $publication_det['publisher_id']);
                $new_product_array[$publication_det['publisher_id']][] = array("id" => $dataDet['id'], "product_id" => $dataDet['product_id'], "user_id" => $dataDet['user_id'], "user_type" => $dataDet['user_type'], "store_id" => $dataDet['store_id'], "quntity" => $dataDet['quntity'], "group_id" => $dataDet['group_id'], "order_id" => $dataDet['group_id']);
            }
        }
        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();
        foreach ($tempData as $dataDet) {
            $order_idsids = $dataDet['order_id'];
            $user_ids_mail[] = $dataDet['user_id'];
            $productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'], $dataDet['store_id']);

            $subscription_data = $modelSubscription->fetchAll('id="' . $dataDet['subscription_type'] . '"');



            if ($dataDet['group_id'] != '' && $dataDet['group_id'] != 0) {
                $arrayDataGroup = '';
                $dateTime = date('Y-m-d H:i:s');
                $price = $productPriceInfo['group_price'];
                $arrayDataGroup = array("publication_id" => $dataDet['product_id'], "group_id" => $dataDet['group_id'], "company_id" => $dataDet['user_id'], "assigned_date" => $dateTime);
                $groupSubsObj->insert($arrayDataGroup);
            } else {
                $price = $productPriceInfo['price'];
            }
            $product_details = $this->modelBooks->fetchRow("id='" . $dataDet['product_id'] . "'");
            $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
            if (!empty($getBrandInfo) && is_numeric($this->$product_details['title'])) {
                $titleBrand = $getBrandInfo['brand'];
            } else {
                $titleBrand = $product_details['title'];
            }
            if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {
                $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);

                $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['publisher_id']);

                if (!empty($getParentBrandInfo)) {
                    //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                    //$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand."<br/>".$authorInfo['publisher'];

                    $titleBrand = $getParentBrandInfo['brand'] . "<br/>" . $authorInfo['publisher'];
                }
            }
            $getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);
            $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
            $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);


            $sql1 = "update pclive_products set best_seller=best_seller+1 where id='" . $dataDet['product_id'] . "'";
            $result1 = $this->modelBooks->getAdapter()->query($sql1);


            $todaysdate = date('Y-m-d H:i:s');

            if ($dataDet['subscription_name'] == 'Weekly') {
                $date = new DateTime($todaysdate);
                $date->modify("+7 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Monthly') {
                $date = new DateTime($todaysdate);
                $date->modify("+30 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Quarterly') {
                $date = new DateTime($todaysdate);
                $date->modify("+90 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Half Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+182 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+365 day");
                $end_date = $date->format("Y-m-d H:i:s");
            }




            if (count($subscription_data) > 0) {
                $price_to_show = $dataDet['subscription_price'];
                $subtype = $dataDet['subscription_name'];
                $subscription_issues = $dataDet['subscription_issues'];
                $todaysdate = $todaysdate;
                $end_date = $end_date;
            } else {
                $price_to_show = $price;
                $subtype = "N/A";
                $subscription_issues = "N/A";
                $todaysdate = "N/A";
                $end_date = "N/A";
            }
            if ($dataDet['user_type'] == "1") {

                $finalsubprice = $price_to_show * $dataDet['quntity'];
            } else {
                $finalsubprice = $price_to_show;
            }
            if ($dataDet['group_id'] != '' && $dataDet['group_id'] != 0) {
                $subscriptiondatavalues = $UsersubObj->fetchAll('order_id=' . $dataDet['order_id'] . ' and product_id=' . $dataDet['product_id'] . '');
                $useremails = "";

                foreach ($subscriptiondatavalues as $userdata) {
                    $user_details = $userCompanyObj->fetchRow("id='" . $userdata['user_id'] . "'");
                    $useremails .= $user_details['user_email'] . ',';
                }
                $useremails = rtrim($useremails, ',');
            } else {
                $useremails = 'N/A';
            }

            $imageInfo = $modelImage->getImageInfoByProductId($dataDet['product_id']);



            $quntity_price = $finalsubprice;
            $price_quan = $producturrency->currencyconverter($getCurrencyName, "NGN", $quntity_price);
            $price_in = $producturrency->currencyconverter($getCurrencyName, "NGN", $price_to_show);
            $total_price = $total_price + $price_quan;
            //if($dataDet['is_purchase']!='1')
            //{
            
            $order_details.= '<tr>
									<td'.$css1.'><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . "/" . USER_UPLOAD_DIR . "thumb1_" . $imageInfo['image_name'] . '"></td>
									<td'.$css1.'>' . $titleBrand . '</td>
									<td'.$css1.'>' . $useremails . '</td>
									<td'.$css1.'>' . $price_in . '</td>
									<td'.$css1.'>' . $subtype . '</td>
									<td'.$css1.'>' . $subscription_issues . '</td>
									<td'.$css1.'>' . $todaysdate . '</td>
									<td'.$css1.'>' . $end_date . '</td>
									<td'.$css1.'>' . $dataDet['quntity'] . '</td>
								</tr>
								';
            //}
            //if($dataDet['is_purchase']!='1')
            //{
            //$quntity_price = $dataDet['quntity']*$price;
            //$order_details.= "<tr><td>".$titleBrand."</td><td>".$producturrency->currencyconverter($getCurrencyName,"NGN",$price)."</td><td>".$dataDet['quntity']."</td></tr><tr><td colspan='3' align='left'>Sub Total: &#x20a6;".@mumber_format($producturrency->currencyconverter($getCurrencyName,"NGN",$quntity_price),2)."</td></tr>";
            //}			
        }
        
        $order_details.='<tr><td colspan="6" align="left">&nbsp;</td><td colspan="2">Sub Total :</td><td> &#x20a6;' . $price_quan . '</td></tr>';    
        $order_details.="<tr><td colspan='6'>&nbsp;</td><td colspan='2' >Total : </td><td>" . $total_price . "</td></tr>";
        $order_details.="</table>";
        $tempObj->delete('order_id="' . $orderIdForPurchase . '"');
        //echo $order_details;
        //exit;
        ###########################################  mail action ############################
        $userCompanyObj = new Company_Model_DbTable_Companies();
        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();
        $storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
        if ($data->id) {
            $user_id = $data->id;
        } elseif ($company_data->id) {
            $user_id = $company_data->id;
        }
        $user_details = $userCompanyObj->fetchRow("id='" . $user_id . "'");
        $mailhost = SMTP_SERVER;
        $mailconfig = array(
            'ssl' => SMTP_SSL,
            'port' => SMTP_PORT,
            'auth' => SMTP_AUTH,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD);
        $transport = new Zend_Mail_Transport_Smtp($mailhost, $mailconfig);
        Zend_Mail::setDefaultTransport($transport);
        $message = '<!DOCTYPE html> 
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Evendor</title>
    </head>
    <body style="font-family: \'Calibri\', arial;">
        <div id="container" style="width:80%; margin:0 auto;">
            <header id="heder">
                <div style="padding:15px 0px; background:#000000; color:#fff;">
                    <a href="' . $this->view->serverUrl() . $this->view->baseUrl() . '/" target="_blank">
                        <img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor">
                    </a>
                </div>
            </header>
            <div style="background:#656565; padding:15px; min-height:200px;color:#fff;">                 
                <span >Hi ' . $user_details['first_name'] . ' ' . $user_details['last_name'] . ',<br/><br/>
                Your order has been successfully completed.Please check your order details.<br><br></span>
                ' . $order_details . '<table border="0" cellpadding="5" style="background:#656565;color:#fff;width:100%;" width="100%"><tr><td colspan="2">Transaction message:' . $responseDescription . '</td></tr>
                <tr><td>Masked PAN:' . $pan . '</td><td>Card Holder Name:' . $card_holder_name . '</td></tr>
                <tr><td>Transaction Date & Time:' . $tranDateTime . '</td><td>Transactions reference number(MerchantTranID):' . $MerchantTranID . '</td></tr>
                <tr><td>Transaction Amount:' . $purchaseAmount . '</td><td>Transaction Currency:' . $currency . '</td></tr>
                <tr><td>Authorization Code:' . $approvalCodeScr . '</td><td>Merchant Name:Evendor</td></tr>
                <tr><td colspan="2">Site Url : <a style="color:#fff;text-decoration:none;" href="' . $this->view->serverUrl() . $this->view->baseUrl() . '">' . $this->view->serverUrl() . $this->view->baseUrl() . '</a></td></tr></table><br/>
                <br/><br/><br/>
                Thank You<br/><br/>
                Evendor                 
            </div>

            <div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">                
                &copy; Copyright ' . date("Y") . ' All Rights Reserved By Electronic Vendor Ltd.
            </div>

        </div>
    </body>
</html>';
			if ($user_details['user_email'] != '') {
			$mail = new Zend_Mail();
			$mail->addTo($user_details['user_email']);
			//$mail->addTo('sumit.upadhyay@magnoninternational.com');
			$mail->setSubject("Order Approved message.");
			$mail->setBodyHtml($message);
			$mail->setFrom(SETFROM, SETNAME);
			$mail->send();
			}

        
        ##############################  admin Message ###################################
        $message_admin = '<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width,initial-scale=1.0">
				<title>Evendor</title>
				</head>
				<body style="font-family: \'Calibri\', arial;">
				<div id="container" style="width:80%; margin:0 auto;">
				<header id="heder">
				<div style="padding:15px 0px; background:#000000;"><a href="' . $this->view->serverUrl() . $this->view->baseUrl() . '/" target="_blank"><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a></div>
				</header>
				<div style="background:#656565; padding:15px; min-height:200px;color:#fff;">
					Hi,<br/><br/>' . $user_details['first_name'] . ' ' . $user_details['last_name'] . ' has been successfully ordered.Please see your order details.<br/><br/>
						' . $order_details . '<br/>Transaction message:' . $responseDescription . '
						<br/>Transaction Date & Time:' . $tranDateTime . '<br/>
						
						<br/>Transactions reference number(MerchantTranID):' . $MerchantTranID . '<br/>
						<br/>Transaction Amount:' . $purchaseAmount . '<br/>
						<br/>Transaction Currency:' . $currency . '<br/>
						<br/>Authorization Code:' . $approvalCodeScr . '<br/>
						<br/>Merchant Name:Evendor<br/>
						<br/>Site Url : <a style="color:#fff;" href="'.$this->view->serverUrl() . $this->view->baseUrl().'">' . $this->view->serverUrl() . $this->view->baseUrl() . '</a><br/>
						<br/><br/><br/>
						Thank You<br/>
						Evendor 
				</div>

				<div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">
				<br>
				&copy; Copyright ' . date("Y") . ' All Rights Reserved By Electronic Vendor Ltd.
				</div>

				</div>
				</body>
				</html>';
        //die($message_admin);
        ##################################################  end admin message #####################
        /*if ($user_details['user_email'] != '') {
            $mail = new Zend_Mail();
            //$mail->addTo($user_details['user_email']);
            $mail->addTo('sumit.upadhyay@magnoninternational.com');
            $mail->setSubject("Order Approved message.");
            $mail->setBodyHtml($message);
            $mail->setFrom(SETFROM, SETNAME);
            $mail->send();
        }*/
        $get_registeration_id = mysql_query("select * from pclive_deviceID where user_publisher_id='" . $user_details['id'] . "' and reg_id!=''");
        while ($res_registeration_id = mysql_fetch_array($get_registeration_id)) {
            $reg = $res_registeration_id['reg_id'];

            $regID = array($reg);
            // $message="You have registerted successfully";
            $message = array("userID" => $user_details['id'], "msg" => "publication is ready to download", "email" => $user_details['user_email'],);

            if ($res_registration_id['os_type'] == 0) {
                send_push_notification($regID, $message);
            } else {
                send_push_notification_ios($regID, $user_details['user_email'], $user_details['id'], "publication is ready to download");
            }
        }

        //$reg='APA91bEZVTcqtar_br_cipLae9TyYe26JhsE2jHHnV76K_zWSAAyly2ZW6SCd4-REMgY_lGtHLh-2VvObjlj-pRvHd2932KlKY7anEOKQMyviVLs1A1Y7RYDPNtXwuRqTY7rILdF_LcXvY4GntxKouBU1EUv8E1mCQ';





        if ($company_data->account_type == '1') {

            $subscriptiondatavalues = $UsersubObj->fetchAll('order_id=' . $orderId . ' and active_date IS NULL');
            foreach ($subscriptiondatavalues as $dataDetsubscribe) {
                $productdetails = $this->modelBooks->fetchRow("id='" . $dataDetsubscribe['product_id'] . "'");

                $getBrandInfo = $this->modelBooks->getBrandInfo($productdetails['title']);
                if (!empty($getBrandInfo) && is_numeric($productdetails['title'])) {
                    $titleBrand = $getBrandInfo['brand'];
                } else {
                    $titleBrand = $productdetails['title'];
                }
                if (!empty($productdetails['parent_brand_id']) && $productdetails['cat_id'] != '3') {
                    $productInfo = $this->modelBooks->fetchRow('id="' . $productdetails['parent_brand_id'] . '"');
                    $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);

                    $authorInfo = $modelAuthor->getInfoByPublisherId($productdetails['publisher_id']);

                    if (!empty($getParentBrandInfo)) {
                        //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                        //$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand."<br/>".$authorInfo['publisher'];
                        $titleBrand = $getParentBrandInfo['brand'] . "<br/>" . $authorInfo['publisher'];
                    }
                }

                $user_details_all = $userCompanyObj->fetchRow("id='" . $dataDetsubscribe['user_id'] . "'");
                $message1 = 'Hi ' . $user_details_all['first_name'] . ' ' . $user_details_all['last_name'] . ', you have been subscribed for the ' . $dataDetsubscribe['subscription_name'] . ' subscription for the product ' . $titleBrand . '  in evendor. You can download ' . $dataDetsubscribe['number_of_issues'] . ' issues. Your subscription starts from ' . $dataDetsubscribe['start_date'] . ' and ends on ' . $dataDetsubscribe['end_date'] . '.Please see your order details.<br/><br/>
				' . $order_details . '';
				
				 
				
				 

                if ($user_details_all['user_email'] != '') {
                    $mail = new Zend_Mail();
                    $mail->addTo($user_details_all['user_email']);
                    //$mail->addTo('sumit@magnoninternational.com');
                    $mail->setSubject("Order Approved message.");
                    $mail->setBodyHtml($message1);
                    $mail->setFrom(SETFROM, SETNAME);
                    $mail->send();
                }

                /* $mail = new Zend_Mail();
                  $mail->addTo('meenakshi@magnoninternational.com');
                  $mail->setSubject("Order Approved message.");
                  $mail->setBodyHtml($message1);
                  $mail->setFrom(SETFROM, SETNAME);
                  $mail->send(); */

                $get_registeration_id = mysql_query("select * from pclive_deviceID where user_publisher_id='" . $user_details_all['id'] . "' and reg_id!=''");
                while ($res_registeration_id = mysql_fetch_array($get_registeration_id)) {
                    $reg = $res_registeration_id['reg_id'];

                    $regID = array($reg);
                    // $message="You have registerted successfully";
                    $message = array("userID" => $user_details_all['id'], "msg" => "publication is ready to download", "email" => $user_details_all['user_email'],);

                    if ($res_registration_id['os_type'] == 0) {
                        send_push_notification($regID, $message);
                    } else {
                        send_push_notification_ios($regID, $user_details['user_email'], $user_details['id'], "publication is ready to download");
                    }

                    //send_push_notification($regID, $message);
                }
                //$reg='APA91bEZVTcqtar_br_cipLae9TyYe26JhsE2jHHnV76K_zWSAAyly2ZW6SCd4-REMgY_lGtHLh-2VvObjlj-pRvHd2932KlKY7anEOKQMyviVLs1A1Y7RYDPNtXwuRqTY7rILdF_LcXvY4GntxKouBU1EUv8E1mCQ';

                /* $reg='APA91bFkjucY1iaCcNprgfIZ_ajoQLv_Ky_jGIH8Y83a3by9fO_ych0yMqG7v9PpJzgttzzbwWZ5SOv6QQV1uYocH4OlyaAsPPi4vvOJILk9YVaVsx2xItJ83_0gXsVDM71LosYKoAqHpdQVx9hXuY5RGXr3LJuzWg';

                  $regID = array($reg);
                  // $message="You have registerted successfully";
                  $message = array("userID" =>$user_details_all['id'],"msg" =>"publication is ready to download","email" =>$user_details_all['user_email'],);
                  send_push_notification($regID, $message); */
            }
        }





        foreach ($new_product_array as $key => $pubVal) {
            $order_details_publisher = "<table cellpadding='1' width='100%' style='background:#656565;color:#fff;'>
										<tr><td>Item</td><td>Price</td><td>Quantity</td></tr>";
            foreach ($pubVal as $pub_order_det) {
                $productPriceInfo = $productPrice->getPriceByStoreId($pub_order_det['product_id'], $pub_order_det['store_id']);
                if ($pub_order_det['group_id'] != '' && $pub_order_det['group_id'] != 0) {
                    $arrayDataGroup = '';
                    $dateTime = date('Y-m-d H:i');
                    $price = $productPriceInfo['group_price'];
                } else {
                    $price = $productPriceInfo['price'];
                }
                $product_details = $this->modelBooks->fetchRow("id='" . $pub_order_det['product_id'] . "'");
                $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
                if (!empty($getBrandInfo) && is_numeric($this->$product_details['title'])) {
                    $titleBrand = $getBrandInfo['brand'];
                } else {
                    $titleBrand = $product_details['title'];
                }
                if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {
                    $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                    $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                    if (!empty($getParentBrandInfo)) {
                        //$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
                        //$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
                        $titleBrand = $getParentBrandInfo['brand'];
                    }
                }
                $getCurrencyName = $producturrency->getCurrencyCode($pub_order_det['store_id']);
                $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
                $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);

                if (count($subscription_data) > 0) {
                    if ($dataDet['group_id'] != '' && $dataDet['group_id'] != 0) {
                        $price_to_show = $subscription_data[0]->group_price_sub;
                    } else {
                        $price_to_show = $subscription_data[0]->individual_price;
                    }
                } else {
                    $price_to_show = $price;
                }
                if ($dataDet['user_type'] == "1") {
                    $finalsubprice = $price_to_show * $dataDet['quntity'];
                } else {
                    $finalsubprice = $price_to_show;
                }

                $quntity_price = $finalsubprice;
                $price_quan = $producturrency->currencyconverter($getCurrencyName, "NGN", $quntity_price);
                $price_in = $producturrency->currencyconverter($getCurrencyName, "NGN", $price_to_show);
                $total_price = $total_price + $price_quan;
                //if($dataDet['is_purchase']!='1')
                //{
                $order_details_publisher.= "<tr>
										<td>" . $titleBrand . "</td>
										<td>" . $price_in . "</td>
										<td>" . $pub_order_det['quntity'] . "</td>
									</tr>
									<tr>
									<td colspan='3' align='left'>Sub Total: &#x20a6;" . $price_quan . "</td></tr>";
            }
            $order_details_publisher.="<tr><td colspan='3'>Total: " . $total_price . "</td></tr>";
            $order_details_publisher.="</table>";

            $message_publisher = '<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width,initial-scale=1.0">
				<title>Evendor</title>
				</head>
				<body style="font-family: \'Calibri\', arial;">
				<div id="container" style="width:80%; margin:0 auto;">
				<header id="heder">
				<div style="padding:15px 0px; background:#000000;"><a href="' . $this->view->serverUrl() . $this->view->baseUrl() . '/" target="_blank"><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a></div>
				</header>
				<div style="background:#656565; padding:15px; min-height:200px;color:#fff;">
					Hi Publisher,<br/><br/>User has been successfully ordered.Please review order details.<br/><br/>
						' . $order_details . '<br/>Transaction message:' . $responseDescription . '
						<br/>Transaction Date & Time:' . $tranDateTime . '<br/>						
						<br/>Merchant Name:Evendor<br/>
						<br/>Site Url : <a style="color:#fff;" href="'.$this->view->serverUrl() . $this->view->baseUrl().'">' . $this->view->serverUrl() . $this->view->baseUrl() . '</a><br/>
						<br/><br/><br/>
						Thank You<br/>
						Evendor
				</div>

				<div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">
				<br>
				&copy; Copyright ' . date("Y") . ' All Rights Reserved By Electronic Vendor Ltd.
				</div>

				</div>
				</body>
				</html>';
				
				 
           // die($message_publisher);
             $mail = new Zend_Mail();
             $mail->addTo('mohan.pal@magnoninternational.com');
               //$mail->addTo('sumit.upadhyay@magnoninternational.com');
              $mail->setSubject("Order Approved message.");
              $mail->setBodyHtml($message_publisher);
              $mail->setFrom(SETFROM, SETNAME);
              $mail->send(); 
        }
        $this->_redirect('checkout/displayorder/orderid/' . $orderIdForPurchase);
        exit;
    }

    public function declinedAction() {
        $producturrency = new Admin_Model_DbTable_Countries();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $tempObj = new Model_DbTable_Checkout();
        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $response = $_POST['xmlmsg'];
        $xml_string = $_POST['xmlmsg'];
        $parsedxml = simplexml_load_string(stripslashes($response));

        $formDataTransDeclined = array();
        foreach ($parsedxml as $RESPONSENODE) {
            if ($RESPONSENODE->getName() == 'OrderID') {
                $orderId = $RESPONSENODE;
                $orderIdForPurchase = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'TransactionType') {
                $transactionType = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'PAN') {
                $pan = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'PurchaseAmountScr') {
                $purchaseAmount = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'TranDateTime') {
                $tranDateTime = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ResponseCode') {
                $responseCode = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ResponseDescription') {
                $responseDescription = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'OrderStatusScr') {
                $orderStatus = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ApprovalCode') {
                $approvalCode = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'MerchantTranID') {
                $merchantTranId = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'OrderDescription') {
                $orderDescription = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ApprovalCodeScr') {
                $approvalCodeScr = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'CurrencyScr') {
                $currency = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ThreeDSVerificaion') {
                $threeDsVerification = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'Brand') {
                $brandname = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'Name') {
                $card_holder_name = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'ThreeDSStatus') {
                $ThreeDSStatus = $RESPONSENODE;
            }
            if ($RESPONSENODE->getName() == 'MerchantTranID') {
                $MerchantTranID = $RESPONSENODE;
            }
        }
        $formDataTransDeclined = array("orderId" => $orderId,
            "transactionType" => $transactionType,
            "pan" => $pan,
            "purchaseAmount" => $purchaseAmount,
            "tranDateTime" => $tranDateTime,
            "responseCode" => $responseCode,
            "responseDescription" => $responseDescription,
            "orderStatus" => $orderStatus,
            "approvalCode" => $approvalCode,
            "approvalCode" => $approvalCode,
            "merchantTranId" => $merchantTranId,
            "orderDescription" => $orderDescription,
            "approvalCodeScr" => $approvalCodeScr,
            "currency" => $currency,
            "brand" => $brandname,
            "card_holder_name" => $card_holder_name,
            "ThreeDSStatus" => $ThreeDSStatus,
            "threeDsVerification" => $threeDsVerification,
            "transaction_xml" => $xml_string);
        //$dataTrans = array("orderId"=>)		
        $inserted_id = $transactionHistoryObj->insert($formDataTransDeclined);

        ##############  update table ###########################
        if ($orderStatus == 'DECLINED') {
            $orderSt = 2;
        } else {
            $orderSt = 0;
        }
        $tempTransactionUpdate = array("payment_status" => $orderSt, "transaction_id" => $inserted_id);
        //$tempObj->update($tempTransactionUpdate,'user_id="'.$user_id.'"');					
        $creditHistoryObj->update($tempTransactionUpdate, "order_id='" . $orderId . "'");

        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();

        ##########################################################
        $tempData = $tempObj->fetchAll("order_id='" . $orderIdForPurchase . "' and is_purchase!='1'");
        $order_details = "<table cellpadding='1' style='background:#656565;color:#fff;'><tr><td colspan='3'>Your reference Id</td></tr><tr><td colspan='3'>Order Id:" . $orderId . "</td></tr><tr><td colspan='3'>Transaction ID:" . $inserted_id . "</td></tr><tr><td>Item Image</td><td>Title</td><td>Price</td><td>Subscription Details</td><td>Subscription Details</td><td>Number of Issues</td><td>Start Date</td><td>End Date</td><td>Quantity</td></tr>";

        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();

        foreach ($tempData as $dataDet) {
            $productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'], $dataDet['store_id']);

            $subscription_data = $modelSubscription->fetchAll('id="' . $dataDet['subscription_type'] . '"');

            if ($dataDet['group_id'] > 0) {
                $price = $productPriceInfo['group_price'];
            } else {
                $price = $productPriceInfo['price'];
            }
            $getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);
            $product_details = $this->modelBooks->fetchRow("id='" . $dataDet['product_id'] . "'");
            $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
            if (!empty($getBrandInfo) && is_numeric($this->$product_details['title'])) {
                $titleBrand = $getBrandInfo['brand'];
            } else {
                $titleBrand = $product_details['title'];
            }
            if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {
                $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                if (!empty($getParentBrandInfo)) {
                    //$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
                    $titleBrand = $getParentBrandInfo['brand'];
                } else {
                    
                }
            }

            $subscription_data = $modelSubscription->fetchAll('id="' . $dataDet['subscription_type'] . '"');

            $todaysdate = date('Y-m-d H:i:s');

            if ($dataDet['subscription_name'] == 'Weekly') {
                $date = new DateTime($todaysdate);
                $date->modify("+7 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Monthly') {
                $date = new DateTime($todaysdate);
                $date->modify("+30 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Quarterly') {
                $date = new DateTime($todaysdate);
                $date->modify("+90 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Half Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+182 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+365 day");
                $end_date = $date->format("Y-m-d H:i:s");
            }

            if (count($subscription_data) > 0) {
                /* if($dataDet['group_id']!='' && $dataDet['group_id']!=0)
                  {
                  $price_to_show = $subscription_data[0]->group_price_sub;
                  }
                  else
                  {
                  $price_to_show = $subscription_data[0]->individual_price;
                  } */

                $price_to_show = $dataDet['subscription_price'];
                $subtype = $dataDet['subscription_name'];
                $subscription_issues = $dataDet['subscription_issues'];
                $todaysdate = $todaysdate;
                $end_date = $end_date;
            } else {
                $price_to_show = $price;
                $subtype = "N/A";
                $subscription_issues = "N/A";
                $todaysdate = "N/A";
                $end_date = "N/A";
            }


            if ($dataDet['user_type'] == "1") {

                $finalsubprice = $price_to_show * $dataDet['quntity'];
            } else {
                $finalsubprice = $price_to_show;
            }

            $imageInfo = $modelImage->getImageInfoByProductId($dataDet['product_id']);

            $quntity_price = $dataDet['quntity'] * $price_to_show;
            $price_quan = $producturrency->currencyconverter($getCurrencyName, "NGN", $quntity_price);
            $price_in = $producturrency->currencyconverter($getCurrencyName, "NGN", $price_to_show);
            $total_price = $total_price + $price_quan;
            //if($dataDet['is_purchase']!='1')
            //{
            $order_details.= '<tr>
									<td><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . "/" . USER_UPLOAD_DIR . 'thumb1_' . $imageInfo['image_name'] . '" alt=""></td>
									<td>' . $titleBrand . '</td>
									<td>' . $price_in . '</td>
									<td>' . $subtype . '</td>
									<td>' . $subscription_issues . '</td>
									<td>' . $todaysdate . '</td>
									<td>' . $end_date . '</td>
									<td>' . $dataDet['quntity'] . '</td>
								</tr>
								<tr>
								<td colspan="3" align="left">Sub Total: &#x20a6;' . $price_quan . '</td></tr>';
            //}
            //if($dataDet['is_purchase']!='1')
            //{
            //$quntity_price = $dataDet['quntity']*$price;
            //$order_details.= "<tr><td>".$titleBrand."</td><td>".$producturrency->currencyconverter($getCurrencyName,"NGN",$price)."</td><td>".$dataDet['quntity']."</td></tr><tr><td colspan='3' align='left'>Sub Total: &#x20a6;".@mumber_format($producturrency->currencyconverter($getCurrencyName,"NGN",$quntity_price),2)."</td></tr>";
            //}	
        }
        $order_details.="<tr><td colspan='3'>Total Price:" . @number_format($producturrency->currencyconverter($getCurrencyName, "NGN", $total_price), 2) . "</td>";
        $order_details.="</table>";
        ###########################################  mail action ############################
        $userCompanyObj = new Company_Model_DbTable_Companies();
        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();
        $storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
        if ($data->id) {
            $user_id = $data->id;
        } elseif ($company_data->id) {
            $user_id = $company_data->id;
        }
        $user_details = $userCompanyObj->fetchRow("id='" . $user_id . "'");

        $mailhost = SMTP_SERVER;
        $mailconfig = array(
            'ssl' => SMTP_SSL,
            'port' => SMTP_PORT,
            'auth' => SMTP_AUTH,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD);
        $transport = new Zend_Mail_Transport_Smtp($mailhost, $mailconfig);
        Zend_Mail::setDefaultTransport($transport);
        $message = '<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width,initial-scale=1.0">
				<title>Evendor</title>
				</head>
				<body style="font-family: \'Calibri\', arial;">
				<div id="container" style="width:80%; margin:0 auto;">
				<header id="heder">
				<div style="padding:15px 0px; background:#000000;"><a href="' . $this->view->serverUrl() . $this->view->baseUrl() . '/" target="_blank"><img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a></div>
				</header>
				<div style="background:#656565; padding:15px; min-height:200px;">		
						Hi ' . $user_details['first_name'] . ' ' . $user_details['last_name'] . ',<br/>
						Your order has been Declined.Please check your order details.<br/><br/>
						' . $order_details . '<br/>Transaction message:' . $responseDescription . '
						<br/>Masked PAN:' . $pan . '<br/>Card Holder Name:' . $card_holder_name . '						
						<br/>Transaction Date & Time:' . $tranDateTime . '<br/>
						<br/>Transactions reference number(MerchantTranID):' . $MerchantTranID . '<br/>
						<br/>Transaction Amount:&#x20a6;' . $purchaseAmount . '<br/>
						<br/>Transaction Currency:' . $currency . '<br/>
						<br/>Authorization Code:' . $approvalCodeScr . '<br/>
						<br/>Merchant Name:Evendor<br/>
						<br/>Site Url:' . $this->view->serverUrl() . $this->view->baseUrl() . '<br/>
						<br/><br/><br/>
						Thank You<br/>
						Evendor 
					
				</div>

				<div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">
				<br>
				&copy; Copyright ' . date("Y") . ' All Rights Reserved By Electronic Vendor Ltd.
				</div>

				</div>
				</body>
				</html>';
        $mail = new Zend_Mail();
        $mail->addTo($user_details['user_email']);
        $mail->setSubject("Order Declined message");
        $mail->setBodyHtml($message);
        $mail->setFrom(SETFROM, SETNAME);
        $mail->send();
        $tempObj->delete('order_id="' . $orderIdForPurchase . '"');



        /* $reg='APA91bFkjucY1iaCcNprgfIZ_ajoQLv_Ky_jGIH8Y83a3by9fO_ych0yMqG7v9PpJzgttzzbwWZ5SOv6QQV1uYocH4OlyaAsPPi4vvOJILk9YVaVsx2xItJ83_0gXsVDM71LosYKoAqHpdQVx9hXuY5RGXr3LJuzWg';


          $get_registeration_id = mysql_query("select * from pclive_deviceID where user_publisher_id='".$user_details_all['id']."' and reg_id!=''");
          while($res_registeration_id=mysql_fetch_array($get_registeration_id))
          {
          $reg= $res_registeration_id['reg_id'];

          $regID = array($reg);
          // $message="You have registerted successfully";
          $message = array("userID" =>$user_details['id'],"msg" =>"publication is ready to download","email" =>$user_details['user_email'],);

          if($res_registration_id['os_type']==0)
          {
          send_push_notification($regID, $message);
          }
          else
          {
          send_push_notification_ios($regID, $user_details['user_email'], $user_details['id'], "publication is ready to download");
          }

          //send_push_notification($regID, $message);
          } */

        /* $regID = array($reg);
          // $message="You have registerted successfully";
          $message = array("userID" =>$user_details['id'],"msg" =>"publication is ready to download","email" =>$user_details['user_email'],);


          send_push_notification($regID, $message); */



        $this->_redirect('/checkout/displayorder/orderid/' . $orderIdForPurchase);
    }

    function displayorderAction() {
        ##########################   data ###########################
        $orderIdForPurchase = $this->_getParam('orderid');
        $producturrency = new Admin_Model_DbTable_Countries();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $tempObj = new Model_DbTable_Checkout();
        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $modelSubscription = new Publisher_Model_DbTable_Subscriptions();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $UsersubObj = new Model_DbTable_Usersubscription();
        $storage_company = new Zend_Auth_Storage_Session('company_type');
        $company_data = $storage_company->read();
        $storage = new Zend_Auth_Storage_Session('account_type');
        $userCompanyObj = new Company_Model_DbTable_Companies();
        $data = $storage->read();
        $useremails = "";
        if ($data->id) {
            $user_id = $data->id;
        } elseif ($company_data->id) {
            $user_id = $company_data->id;
        }

        if ($company_data->account_type == '1') {
            $groupObj = new Company_Model_DbTable_Groups();
            $groupList = $groupObj->getGroupList($company_data->id);
        }
        $datahistory = "<div class='display-order'><div class='top_heading'>				
		    	<div class='item'>Item(s)</div>
				<div class='prices'>Subscribed Users</div>
		        <div class='prices'>Subscription Type</div>
		        <div class='prices'>Price</div>";
        if ($company_data->account_type == '1') {

            $datahistory.="<div class='quantity'>Qty</div>";
        } else {
            $datahistory.="<div class='quantity'>&nbsp;</div>";
        }
        $datahistory.="<div class='total'>Total</div>
		    </div>";

        $orderData = $creditHistoryObj->fetchAll("order_id='" . $orderIdForPurchase . "'");
        $dataDet = '';
        foreach ($orderData as $dataDet) {
            $price = $dataDet['price'];

            $subscription_data = $modelSubscription->fetchAll('id="' . $dataDet['subscription_type'] . '"');

            $product_details = $this->modelBooks->fetchRow("id='" . $dataDet['bookid'] . "'");
            $transaction_details = $transactionHistoryObj->fetchRow("id='" . $dataDet['transaction_id'] . "'");
            $catInfo = $this->modelBooks->getCategoryInfo($product_details['cat_id']);
            $getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);
            $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['publisher_id']);
            $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
            $total_price = $total_price + $producturrency->currencyconverter($getCurrencyName, "NGN", $dataDet['quantity'] * $price);
            if ($dataDet['payment_status'] == 1) {
                $orderStatus = 'Approved';
            } elseif ($dataDet['payment_status'] == 2) {
                $orderStatus = 'Declined';
            } else {
                $orderStatus = 'Pending';
            }

            if (count($subscription_data) > 0) {
                $subtype = $subscription_data[0]->subscription_type;
            } else {
                $subtype = "N/A";
            }
            if ($company_data->account_type == '1') {
                $subscriptiondatavalues = $UsersubObj->fetchAll('order_id=' . $orderIdForPurchase . ' and product_id=' . $dataDet['bookid'] . '  and active_date IS NULL');

                foreach ($subscriptiondatavalues as $userdata) {
                    $user_details = $userCompanyObj->fetchRow("id='" . $userdata['user_id'] . "'");
                    $useremails .= $user_details['user_email'] . '<br/>';
                }
                $useremails = rtrim($useremails, '<br/>');
            } else {
                $user_details = $userCompanyObj->fetchRow("id='" . $user_id . "'");
                $useremails = $user_details['user_email'];
            }
            $datahistory.='<div class="cart_detail">
		    	<div class="item_content">
		   	    	<img src="' . $this->view->serverUrl() . $this->view->baseUrl() . '/' . USER_UPLOAD_DIR . 'thumb1_' . $imageInfo['image_name'] . '" width="140" height="175" alt="">
		            <h5>' . stripslashes($dataDet['book_name']) . '</h5>';
            if ($authorInfo['first_name'] != '') {
                $datahistory.='<span><em>by: </em> ' . stripslashes($authorInfo['first_name'] . '&nbsp;' . $authorInfo['last_name']) . '</span>';
            }
            $datahistory.='<span><em>Category: </em> ' . stripslashes($catInfo['category_name']) . '</span>
					<span><em>publisher:</em> ' . stripslashes($authorInfo['publisher']) . '</span>
		           </div>     
				   <div class="space_content">' . $useremails . '</div>
				<div class="prices_content">' . $subtype . '</div>
		        <div class="prices_content">&#x20a6;' . $producturrency->currencyconverter($getCurrencyName, "NGN", $price) . '</div>
		        <div style="float: left;padding: 5% 0;text-align: center;width: 70px;">
		        	<div class="">';
            if ($dataDet['group_id'] != 0) {
                $datahistory.='<div >' . $dataDet['quantity'] . '</div>';
            }
            $datahistory.='</div>
		        </div>
		        <div class="total_content">&#x20a6;' . @number_format($producturrency->currencyconverter($getCurrencyName, "NGN", ($dataDet['quantity'] * $price)), 2) . '</div>    
		 		</div>';

            if ($company_data->account_type == '1') {
                $finalsubprice = $finalsubprice + ($dataDet['quantity'] * $price);
            } else {
                $finalsubprice = $finalsubprice + $price;
            }
        }


        $datahistory.='<div class="shipping_total">
					<div class="row">
				    	<div class="lt">Subtotal</div>
				        <div class="rt">&#x20a6;' . @number_format($total_price, 2) . '</div>
				    </div>
				 		    
				    <div class="row" style="border:none;">
				    	<div class="lt">Tax</div>
				        <div class="rt">&#x20a6;' . '00.00</div>
				    </div>
				    
				    <div class="row subtotal">
				    	<div class="lt">Total</div>
				        <div class="rt">&#x20a6;' . @number_format($total_price, 2) . '</div>
				    </div>	
				      <div class="row subtotal">
				    	<div class="lt">Order Id</div>
				        <div class="rt">' . $orderIdForPurchase . '</div>
				    </div>
				      <div class="row subtotal">
				    	<div class="lt">Payment Status</div>
				        <div class="rt">' . $orderStatus . '</div>
				    </div>		   
				</div></div>';
        $this->view->datahistory = $datahistory;
        $this->view->orderId = $orderIdForPurchase;
        $this->view->transactionId = $orderData[0]['transaction_id'];
        $this->view->orderStatus = $orderStatus;
        $this->view->responseDescription = $transaction_details['responseDescription'];
    }

    public function freepurchaseAction() {
        $producturrency = new Admin_Model_DbTable_Countries();
        $modelAuthor = new Publisher_Model_DbTable_Publishers();
        $tempObj = new Model_DbTable_Checkout();
        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
        $this->modelBooks = new Publisher_Model_DbTable_Books();
        $modelImage = new Publisher_Model_DbTable_BookImages();
        $modelStore = new Admin_Model_DbTable_Countries();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $productPrice = new Publisher_Model_DbTable_BookPrices();
        $orderId = time();

        $subscription_store = "";
        $subscription_language = "";
        $subscription_issues = "";


        $formDataTransApproved = array("orderId" => $orderId,
            "transactionType" => $transactionType,
            "pan" => $pan,
            "purchaseAmount" => $purchaseAmount,
            "tranDateTime" => $tranDateTime,
            "responseCode" => $responseCode,
            "responseDescription" => $responseDescription,
            "orderStatus" => $orderStatus,
            "approvalCode" => $approvalCode,
            "approvalCode" => $approvalCode,
            "merchantTranId" => $merchantTranId,
            "orderDescription" => $orderDescription,
            "approvalCodeScr" => $approvalCodeScr,
            "currency" => $currency,
            "threeDsVerification" => $threeDsVerification);

        $inserted_id = $transactionHistoryObj->insert($formDataTransApproved);
        $tempData = $tempObj->fetchAll("order_id='" . $orderIdForPurchase . "'");
        foreach ($tempData as $dataDet) {
            $productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'], $dataDet['store_id']);
            if ($dataDet['subscription_type'] == '' || $dataDet['subscription_type'] == '0') {
                if ($dataDet['group_id'] > 0) {
                    $price = $productPriceInfo['group_price'];
                } else {
                    $price = $productPriceInfo['price'];
                }
            } else {
                $price = $dataDet['subscription_price'];
                $subscription_store = $dataDet['subscription_store'];
                $subscription_language = $dataDet['subscription_language'];
                $subscription_issues = $dataDet['subscription_issues'];
            }
            if ($orderStatus == 'APPROVED') {
                $orderSt = 1;
            } else {
                $orderSt = 0;
            }
            $product_details = $this->modelBooks->fetchRow("id='" . $dataDet['product_id'] . "'");
            $getBrandInfo = $this->modelBooks->getBrandInfo($product_details['title']);
            if (!empty($getBrandInfo) && is_numeric($this->$product_details['title'])) {
                $titleBrand = $getBrandInfo['brand'];
            } else {
                $titleBrand = $product_details['title'];
            }
            if (!empty($product_details['parent_brand_id']) && $product_details['cat_id'] != '3') {
                $productInfo = $this->modelBooks->fetchRow('id="' . $product_details['parent_brand_id'] . '"');
                $getParentBrandInfo = $this->modelBooks->getBrandInfo($productInfo->title);
                if (!empty($getParentBrandInfo)) {
                    //$titleBrand= $getParentBrandInfo['brand'] ." - ".$titleBrand;
                    $titleBrand = $getParentBrandInfo['brand'];
                }
            }
            $getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);
            $authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
            $imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);

            $tempDatInsert = array();
            $tempDatInsert['userid'] = $dataDet['user_id'];
            $tempDatInsert['bookid'] = $dataDet['product_id'];
            $tempDatInsert['store_id'] = $dataDet['store_id'];
            $tempDatInsert['price'] = $price;
            $tempDatInsert['userid'] = $dataDet['user_id'];
            $tempDatInsert['quantity'] = $dataDet['quntity'];
            $tempDatInsert['book_name'] = $titleBrand;
            $tempDatInsert['transaction_id'] = $inserted_id;
            $tempDatInsert['add_date'] = date('Y-m-d H:i:s');
            $tempDatInsert['group_id'] = $dataDet['group_id'];
            $tempDatInsert['order_id'] = $dataDet['order_id'];
            $tempDatInsert['payment_status'] = $orderSt;
            $tempDatInsert['subscription_type'] = $dataDet['subscription_type'];
            $tempDatInsert['subscription_name'] = $dataDet['subscription_name'];
            $tempDatInsert['subscription_store'] = $dataDet['subscription_store'];
            $tempDatInsert['subscription_language'] = $dataDet['subscription_language'];
            $tempDatInsert['subscription_issues'] = $dataDet['subscription_issues'];
            $creditHistoryObj->insert($tempDatInsert);
            $total_price = $total_price + ($dataDet['quntity'] * $price);




            $todaysdate = date('Y-m-d H:i:s');

            if ($dataDet['subscription_name'] == 'Weekly') {
                $date = new DateTime($todaysdate);
                $date->modify("+7 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Monthly') {
                $date = new DateTime($todaysdate);
                $date->modify("+30 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Quarterly') {
                $date = new DateTime($todaysdate);
                $date->modify("+90 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Half Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+182 day");
                $end_date = $date->format("Y-m-d H:i:s");
            } else if ($dataDet['subscription_name'] == 'Yearly') {
                $date = new DateTime($todaysdate);
                $date->modify("+365 day");
                $end_date = $date->format("Y-m-d H:i:s");
            }


            if ($company_data->account_type == '1') {
                $explode_users = explode(",", $dataDet['group_user_selected']);
                for ($x = 0; $x < count($explode_users); $x++) {
                    $subscriptionObj['order_id'] = $orderid;
                    $subscriptionObj['product_id'] = $dataDet['product_id'];
                    $subscriptionObj['group_id'] = $dataDet['group_id'];
                    $subscriptionObj['user_id'] = $explode_users[$x];
                    $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                    $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                    $subscriptionObj['subscription_price'] = $price;
                    $subscriptionObj['country'] = $dataDet['subscription_store'];
                    $subscriptionObj['language'] = $dataDet['subscription_language'];
                    $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                    $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                    $subscriptionObj['end_date'] = $end_date;
                    $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                    $subscriptionObj['publication_id'] = $product_details['parent_brand_id'];
                    $subscriptionObj['company_id'] = $dataDet['company_id'];

                    if ($dataDet['subscription_type'] == 0) {
                        $checksubscription = $UsersubObj->fetchAll('product_id="' . $dataDet['product_id'] . '" and user_id="' . $explode_users[$x] . '"');
                    }

                    if ($dataDet['subscription_type'] > 0) {
                        $checksubscription = $UsersubObj->fetchAll('user_id="' . $explode_users[$x] . '" and publication_id="' . $product_details['parent_brand_id'] . '"');
                    }

                    if (count($checksubscription) == 0) {
                        $UsersubObj->insert($subscriptionObj);
                    } else {
                        $totalissues_remaining = $checksubscription[0]['remaining_downloads'] + $dataDet['subscription_issues'];

                        $subscriptionObj['product_id'] = $dataDet['product_id'];
                        $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                        $subscriptionObj['author_id'] = $product_details['author_id'];

                        $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                        $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                        $subscriptionObj['subscription_price'] = $price;
                        $subscriptionObj['country'] = $dataDet['subscription_store'];
                        $subscriptionObj['language'] = $dataDet['subscription_language'];
                        $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                        $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                        $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                        $subscriptionObj['end_date'] = $end_date;

                        $condition = 'user_id=' . $explode_users[$x] . ' and publication_id=' . $product_details['parent_brand_id'] . '';

                        $UsersubObj->update($subscriptionObj, $condition);
                    }
                }
            } else {
                $subscriptionObj = array();
                $subscriptionObj['order_id'] = $orderid;
                $subscriptionObj['product_id'] = $dataDet['product_id'];
                $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                $subscriptionObj['author_id'] = $product_details['author_id'];
                $subscriptionObj['user_id'] = $dataDet['user_id'];
                $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                $subscriptionObj['subscription_price'] = $price;
                $subscriptionObj['country'] = $dataDet['subscription_store'];
                $subscriptionObj['language'] = $dataDet['subscription_language'];
                $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                $subscriptionObj['end_date'] = $end_date;
                $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                $subscriptionObj['publication_id'] = $product_details['parent_brand_id'];

                if ($dataDet['subscription_type'] == 0) {
                    $checksubscription = $UsersubObj->fetchAll('product_id="' . $dataDet['product_id'] . '" and user_id="' . $dataDet['user_id'] . '"');
                }

                if ($dataDet['subscription_type'] > 0) {
                    $checksubscription = $UsersubObj->fetchAll('user_id="' . $dataDet['user_id'] . '" and publication_id="' . $product_details['parent_brand_id'] . '"');
                }

                if (count($checksubscription) == 0) {
                    $UsersubObj->insert($subscriptionObj);
                } else {
                    $totalissues_remaining = $checksubscription[0]['remaining_downloads'] + $dataDet['subscription_issues'];

                    $subscriptionObj['product_id'] = $dataDet['product_id'];
                    $subscriptionObj['publisher_id'] = $product_details['publisher_id'];
                    $subscriptionObj['author_id'] = $product_details['author_id'];

                    $subscriptionObj['subscription_type'] = $dataDet['subscription_type'];
                    $subscriptionObj['subscription_name'] = $dataDet['subscription_name'];
                    $subscriptionObj['subscription_price'] = $price;
                    $subscriptionObj['country'] = $dataDet['subscription_store'];
                    $subscriptionObj['language'] = $dataDet['subscription_language'];
                    $subscriptionObj['number_of_issues'] = $dataDet['subscription_issues'];
                    $subscriptionObj['remaining_downloads'] = $dataDet['subscription_issues'];
                    $subscriptionObj['start_date'] = date('Y-m-d H:i:s');
                    $subscriptionObj['end_date'] = $end_date;

                    $condition = 'user_id=' . $dataDet['user_id'] . ' and publication_id=' . $product_details['parent_brand_id'] . '';



                    $UsersubObj->update($subscriptionObj, $condition);
                }
            }
        }
        $tempObj->delete('order_id="' . $orderIdForPurchase . '"');
        $this->_redirect('/checkout/displayorder/orderid/' . $orderIdForPurchase);
        //exit;
    }

    public function storevaluesAction() {
        
    }

    public function addfieldAction() {

        $creditHistoryObj = new User_Model_DbTable_Chistory();
        $re = $creditHistoryObj->fetchAll();
        //echo "<pre>";
        //print_r($re);
        //exit;
        $db = Zend_Registry::get('db');
        $creditHistoryObj->addnewField();
        exit;
    }

}
