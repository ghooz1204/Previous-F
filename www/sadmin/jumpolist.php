<?php
@extract($_GET);
@extract($_POST);


    $get_field = escape_string($_REQUEST['find_field']);
    $get_word  = escape_string($_REQUEST['find_word'],'1');
    $get_ordby = escape_string($_REQUEST['ordby']);
	$get_viewtype = escape_string($_REQUEST['find_viewtype']);
    if(empty($get_ordby)){ $get_ordby = escape_string($_REQUEST['find_ordby']); }

    $get_page  = escape_string($_GET['page']);
    $get_plist = escape_string($_REQUEST['plist']);

    $pagePerBlock = "10";
	if($BOARD_TYPE=="PHOTO") {
	    empty($get_plist) ? $pagesize = "12" :  $pagesize = $get_plist; /// 페이지출력수
	} else {
	    empty($get_plist) ? $pagesize = "20" :  $pagesize = $get_plist; /// 페이지출력수
	}
    empty($get_page) ? $page = 1 : $page = $get_page;  /// 페이지번호

    $pageurl = $_SERVER['PHP_SELF'];
    $perpage = ($page-1) * $pagesize;
    $search = "plist=".$get_plist."&find_field=".$get_field."&find_word=".$get_word."&find_state=".$find_state."&find_ordby=".$get_ordby."&conf=".$conf;

    ///정렬
    if(empty($get_ordby)){
        $ORDER_BY = "";
    }else{
        $ORDER_BY = str_replace("__"," ",$get_ordby).",";
     }

    if(!empty($get_viewtype)){
		//상태정보
        $where_add .= " AND viewtype = '".$find_viewtype."'";
    }

    ///검색정보
    $where_add ="";

    if(!empty($get_field) && !empty($get_word)){
        $where_add .= " AND ".$get_field." like '%".$get_word."%'";
    }

     /// 갯수뽑기용 쿼리
    $query = "SELECT * FROM  $tablename  WHERE 1  ".$where_add." ORDER BY ".$ORDER_BY." uid DESC";
	//echo "$query /<br>";
    $result_cnt = $db->fetch_array( $query );
    $total_num = count($result_cnt) ;

    if(!empty($total_num)){
        //연결 URL, 총번호, 한페이지갯수, 최대페이지번호, 현재페이지, 서치
        $pageNavi = $common->paging( $pageurl, $total_num, $pagesize, $pagePerBlock, $page, $search);
        $pageNaviHTML = '<div class="paging-box"><ul>'.$pageNavi.'</ul></div>'; ///페이징
        //목록 출력 적용
        $limit = "limit $perpage, $pagesize";
        $numbers = $total_num - $perpage;

    // 전체 쿼리에 제한 걸기
    $query = $query ." ".$limit;
	//echo "$query ///<br>";

        $result = $db->fetch_array( $query );
        $rcount = count($result) ;
    }
?>
	<form name="listform" method="post"> 	
	  <table width="880" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="10"><img src="img/admin_12.gif" /></td>
		  <td width="33" align="center" background="img/admin_14.gif"><input type="checkbox" name="allCheck" value="all" onclick="checkall(this.form)"></td>
          <td width="66" align="center" background="img/admin_14.gif"><font  class="T4">번호</font></td>
		<?if($IMGLIST=="Y") {?>	
		  <td width="100" align="center" background="img/admin_14.gif"><font  class="T4">이미지</font></td>
		<?}?>		
          <td align="center" background="img/admin_14.gif"><font  class="T4">매장명</font></td>
          <td width="105" align="center" background="img/admin_14.gif"><font  class="T4">등록일</font></td>
          <td width="10"><img src="img/admin_13.gif" /></td>
        </tr>
	

	<?
        for ( $i=0 ; $i<$rcount ; $i++ ) {
		$link_page = "$_SERVER[PHP_SELF]?bmain=write&uid=".$result[$i][uid];

		if($MEMOUSETYPE=="Y") {
			#### 댓글 사용하는 경우 갯수 조회 ########
			$query_tot = "SELECT uid FROM  $memo_tablename  WHERE board_uid='".$result[$i][uid]."'";
			$result_memo_count = $db->num_rows( $query_tot );
			if($result_memo_count) $result_memo_count = "(".$result_memo_count.")"; else $result_memo_count="";
		}

		if($MODEUSETYPE=="Y"){	//분류사용인경우
			$row_board_mode = "[".$ARR_BOARD_TYPE[$result[$i][mode]]."] ";
		}

	?>

        <tr height="33">
		  <td ></td>	
		  <td align="center"><input type="checkbox" name="chklist" class=border value="<?=$result[$i][uid]?>"></td>
          <td align="center"><?=$numbers?></td>
		<?if($IMGLIST=="Y") {?>	
          <td align="center">
				<table width="100" border="0" cellspacing="1" cellpadding="1">
				<tr>
				<td height="70" align="center" style="border:1px solid #D5D5D5"><a href="<?=$link_page?>" class="L4">
				<? 
				if($result[$i][fileadd_name]) {
					$row_file = explode("|",$result[$i][fileadd_name]);
					for($f=0;$f<1;$f++) {
						if($row_file[$f]) {
						$fname_value = "<img src='../$tablefile/$row_file[$f]' width=100 height=80></td>";
						echo "$fname_value";
						}
					}

					} else {
					echo "<img src=$HOME_PATH/Bimg/no_image.gif border=0 width=100 height=80></a></td>";
					}
				?>						 
				</td>
				</tr>
				</table>
		  </td>
		<?}?>
          <td>&nbsp;	
			<a href="<?=$link_page?>" class="L4"><?=$row_board_mode?><?=$result[$i][title]?></a> <?=$row_reply?> <?=$result_memo_count?></td>
          <td align="center"><a href="<?=$link_page?>"><?=substr($result[$i][reg_date],0,10)?></a></td>
          <td>&nbsp;</td>
        </tr>
        <tr> 
          <td height="1" colspan="8" bgcolor="#ebebeb"></td>
        </tr>
	<?
		$numbers--; //paging에 따른 번호
		} //end for ?>

        
      </table>
	</form>
      <br />
      <br />


      <table width="880" height="25" border="0" cellpadding="0" cellspacing="0">
        <tr>
		  <td width="55"><!-- 삭제 --><a href="javascript:select_member_change('board_delete','boardok.php?conf=<?=$conf?>&formmode=delete_all');"><img src="./img/btn_7.gif"  border="0" alt='삭제' /></a></td>
          <td align=center><?=$pageNaviHTML;?></td>
          <td width="55"><a href="<?echo"$_SERVER[PHP_SELF]?bmain=write"?>"><img src="img/btn_3.gif" width="55" height="25" border="0" /></a></td>
        </tr>
      </table>
      <br />
      <br />

	  <form method="POST" action="?" id="searchForm">
      <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="img/admin_28.gif">
        <tr>
          <td width="1%"><img src="img/admin_27.gif" width="20" height="53" /></td>
          <td width="98%" valign="top">
			<table width="317" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>				
                <td width="67" height="42">
					<select name="find_field" class="FORM3">
						<option value="title" <?=($get_field=="title"  )?"selected":"";?> >매장명</option>
						<option value="content" <?=($get_field=="content")?"selected":"";?> >내용</option>
					</select>
                </td>
                <td width="203"><input name="find_word" type="text" size="30" class="FORM1" value="<?=$get_word;?>" />
                </td>
                <td width="47"><input type="image" src="img/admin_29.gif" border="0" /></td>
              </tr>
          </table></td>
          <td width="1%"><img src="img/admin_26.gif" width="20" height="53" /></td>
        </tr>
      </table>

		<input type="hidden" name="plist">
		<input type="hidden" name="ordby">
		<input type="hidden" name="conf" value="<?=$conf?>"><!-- 환경설정파일  -->
	</form>


	<SCRIPT type="text/javascript">
		$(document).ready(function(){
			//한페이지에 커서
			$('select[name="selectRow"]').val("<?=$pagesize;?>").attr("selected", "selected");

			//한페이지에 검색폼으로 값보내기
			$('select[name="selectRow"]').change(function(){
				var selectRowVal = $(this).val();
				$('input[name="plist"]').val(selectRowVal);
				$("#searchForm").submit();
			});
	
		});
	</SCRIPT>
