<?php
@extract($_GET);
@extract($_POST);


    $get_field = escape_string($_REQUEST['find_field']);
    $get_word  = escape_string($_REQUEST['find_word'],'1');
    $get_ordby = escape_string($_REQUEST['ordby']);
	$get_viewtype = escape_string($_REQUEST['find_viewtype']);
    $get_mode1  = escape_string($_REQUEST['find_mode1'],'1');

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
    $search = "plist=".$get_plist."&find_field=".$get_field."&find_word=".$get_word."&find_state=".$find_state."&find_ordby=".$get_ordby."&conf=".$conf."&find_mode1=".$find_mode1;

    ///검색정보
    $where_add ="";


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

	if($mode_a) {
        $where_add .= " AND mode = '".$mode_a."'";
	}
	if($get_mode1) {
        $where_add .= " AND mode1 = '".$get_mode1."'";
	}


    if(!empty($get_field) && !empty($get_word)){
        $where_add .= " AND ".$get_field." like '%".$get_word."%'";
    }

     /// 갯수뽑기용 쿼리
    $query = "SELECT * FROM  $tablename  WHERE topview!='Y' ".$where_add." ORDER BY ".$ORDER_BY." fidnum DESC, thread ASC";
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

<?if($BOARD_TYPE=="EVENTPHOTO") {?>
	<form name="listform" method="post">
	<table height="98" border="0" cellpadding="5" cellspacing="0">
	<?
        for ( $i=0 ; $i<$rcount ; $i++ ) {
		$link_page = "$_SERVER[PHP_SELF]?bmain=view&uid=".$result[$i][uid];

		if($MEMOUSETYPE=="Y") {
			#### 댓글 사용하는 경우 갯수 조회 ########
			$query_tot = "SELECT uid FROM  $memo_tablename  WHERE board_uid='".$result[$i][uid]."'";
			$result_memo_count = $db->num_rows( $query_tot );
			if($result_memo_count) $result_memo_count = "(".$result_memo_count.")"; else $result_memo_count="";
		}

		$row_board_mode = "";
		if($MODEUSETYPE1=="Y"){	//분류사용인경우
			if($result[$i][mode1]) $row_board_mode = "[".$ARR_BOARD_TYPE1[$result[$i][mode1]]."] ";
		}

	?>
		<?
			if($td == 0 ) {
				echo("<tr> ");
			}
			if(($td%2) == 0) {
				echo("<tr>  ");
			}

			if($result[$i][fileadd_name]) {
				$board_img = "$HOME_PATH/$tablefile/".$result[$i][fileadd_name];
			} else {
				$board_img = "../images/bbs1_42.gif";
			}
		?>

          <td>
			<table width="50%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td height="152" bgcolor="#CCCCCC" align="center"><a href="<?=$link_page?>" ><img src="<?=$board_img?>" width="420" height="150" /></a></td>
              </tr>
              <tr>
                <td height="46" valign="bottom"><input type="checkbox" name="chklist" class=border value="<?=$result[$i][uid]?>"><a href="<?=$link_page?>"  class="L2"><?=$row_board_mode?><?=$common->cut_string($result[$i][title],12)?></a> <?=$result_memo_count?></a><br />
                    <?=$result[$i][sdate]?>~<?=$result[$i][edate]?></td>
              </tr>
            </table>
		  </td>

		<?
			$td += 1;
			if($td == 0) {
				echo("</tr> ");
			}
			if(($td%2) == 0) {
				echo("</tr> <tr><td height=20></td></tr> ");
			}
		}//end for
		?>
      </table>
	  </form>


<?} else {?>
<!-- ================ 리스트 형태 출력인 경우 ======================= -->

	<form name="listform" method="post"> 	
	  <table width="880" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="10"><img src="img/admin_12.gif" /></td>
		  <td width="33" align="center" background="img/admin_14.gif"><input type="checkbox" name="allCheck" value="all" onclick="checkall(this.form)"></td>
          <td width="66" align="center" background="img/admin_14.gif"><font  class="T4">번호</font></td>
		<?if($IMGLIST=="Y") {?>	
		  <td width="100" align="center" background="img/admin_14.gif"><font  class="T4">이미지</font></td>
		<?}?>
		
          <td align="center" background="img/admin_14.gif"><font  class="T4">제목</font></td>
          <td width="105" align="center" background="img/admin_14.gif"><font  class="T4">등록일</font></td>
          <td width="96" align="center" background="img/admin_14.gif"><font  class="T4">작성자</font></td>
		<?if($REFUSETYPE=="Y") {?>	
          <td width="70" align="center" background="img/admin_14.gif"><font  class="T4">조회수</font></td>
		<?}?>
          <td width="70" align="center" background="img/admin_14.gif"><font  class="T4">출력</font></td>
          <td width="10"><img src="img/admin_13.gif" /></td>
        </tr>
	<!-- 상단 출력인 경우 사용  -->
	<?
     /// 갯수뽑기용 쿼리
    $query1 = "SELECT * FROM  $tablename  WHERE topview='Y' ".$where_add." ORDER BY ".$ORDER_BY." fidnum DESC, thread ASC";
	//echo "$query /<br>";
       $result1 = $db->fetch_array( $query1);
       $rcount1 = count($result1) ;

        for ( $t=0 ; $t<$rcount1 ; $t++ ) {
		$link_page = "$_SERVER[PHP_SELF]?bmain=view&uid=".$result1[$t][uid];

		if($MEMOUSETYPE=="Y") {
			#### 댓글 사용하는 경우 갯수 조회 ########
			$query_tot = "SELECT uid FROM  $memo_tablename  WHERE board_uid='".$result1[$t][uid]."'";
			$result_memo_count = $db->num_rows( $query_tot );
			if($result_memo_count) $result_memo_count = "(".$result_memo_count.")"; else $result_memo_count="";
		}
	?>
        <tr height="33">
		  <td ></td>		
          <td  align="center"><input type="checkbox" name="chklist" class=border value="<?=$result1[$t][uid]?>"></td>
          <td align="center"><b>[공지]</b></td>
          <td ><a href="<?=$link_page?>"><?=$result1[$t][title]?></a> <?=$result_memo_count?></td>
          <td align="center"><a href="<?=$link_page?>"><?=substr($result1[$t][reg_date],0,10)?></a></td>
          <td align="center"><?=$result1[$t][uname]?></td>
		<?if($REFUSETYPE=="Y") {?>
          <td align="center"><?=$result1[$t][ref]?></td>
		<?}?>
          <td align="center"><?=$result1[$t][viewtype]?></td>
        </tr>
        <tr> 
          <td height="1" colspan="8" bgcolor="#ebebeb"></td>
        </tr>
	<?}?>
	<!-- end 상단 출력인 경우 사용  -->

	<?
        for ( $i=0 ; $i<$rcount ; $i++ ) {
		$link_page = "$_SERVER[PHP_SELF]?bmain=view&uid=".$result[$i][uid];

		if($MEMOUSETYPE=="Y") {
			#### 댓글 사용하는 경우 갯수 조회 ########
			$query_tot = "SELECT uid FROM  $memo_tablename  WHERE board_uid='".$result[$i][uid]."'";
			$result_memo_count = $db->num_rows( $query_tot );
			if($result_memo_count) $result_memo_count = "(".$result_memo_count.")"; else $result_memo_count="";
		}
		
		$row_board_mode = "";
		if($MODEUSETYPE1=="Y"){	//분류사용인경우
			if($result[$i][mode1]) $row_board_mode = "[".$ARR_BOARD_TYPE1[$result[$i][mode1]]."] ";
		}

	if($CONTENT2TYPE!="Y") { 
		$row_reply = "";
		if($result[$i][content1]) {
			$row_reply = "(답변완료)";
		}
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
					echo "<img src='$HOME_PATH/$tablefile/".$result[$i][fileadd_name]."' border=0 width=100 height=80></a></td>";
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
			<?
				$reply_space=5;
				########## 답변 단계에 따라서 안으로 한칸씩 밀어서 출력 ##########
				 $space = strlen($result[$i][thread])-1;
				 if($space > $reply_space)
					 $space = $reply_space;  //답변글이 $reply_space 값 이상이 되면  출력 간격을 고정
				 if($space>0){
					 for($j = 0; $j < $space; $j++) {
						echo("&nbsp;");
					 }
					 echo("&nbsp;&nbsp;<img src=../images/admin_39.gif>");
				 }
			?>
			<a href="<?=$link_page?>" class="L4"><?=$row_board_mode?><?=$result[$i][title]?></a> <?=$row_reply?> <?=$result_memo_count?></td>
          <td align="center"><a href="<?=$link_page?>"><?=substr($result[$i][reg_date],0,10)?></a></td>
          <td align="center"><a href="<?=$link_page?>"><?=$result[$i][uname]?></a></td>
		<?if($REFUSETYPE=="Y") {?>
          <td align="center"><a href="<?=$link_page?>"><?=$result[$i][ref]?></a></td>
		<?}?>
          <td align="center"><a href="<?=$link_page?>"><?=$result[$i][viewtype]?></a></td>
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
<!-- ================ end 리스트 형태 출력인 경우 ======================= -->
<?} //end if($BOARD_TYPE=="PHOTO") { ?>

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
			<table width="567" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
				<td width=250>
					<?if($MODEUSETYPE1=="Y"){?>
					<select name="find_mode1" class="FORM1">
					  <option value="">-국가그룹별보기-</option>
					<? for($j=1;$j<=count($ARR_BOARD_TYPE1);$i++,$j++) { ?>
						<option value="<?=$j?>" <?if($get_mode1=="$j") echo"selected";?>><?=$ARR_BOARD_TYPE1[$j]?></option>
					<?}?>
					</select>
					<?}?>

					<select name="find_viewtype" class="FORM1">
					  <option value="">-출력상태-</option>
					  <option value="Y" <?=($get_viewtype=="Y")?"selected":"";?>>출력</option>
					  <option value="N" <?=($get_viewtype=="N")?"selected":"";?>>숨기기</option>
					</select>
				</td>
                <td width="67" height="42">
					<select name="find_field" class="FORM3">
						<option value="title" <?=($get_field=="title"  )?"selected":"";?> >제목</option>
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
