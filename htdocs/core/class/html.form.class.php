<?php

class Form
{
	
    public $db;

	
	public $error = '';

    
    public $errors = array();

	public $num;

	  
	public $cache_types_paiements = array();
	public $cache_conditions_paiements = array();
	public $cache_availability = array();
	public $cache_demand_reason = array();
	public $cache_types_fees = array();
	public $cache_vatrates = array();


	/**
	 * Constructor
	 *
	 * @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	
    public function editfieldkey($text, $htmlname, $preselected, $object, $perm, $typeofdata = 'string', $moreparam = '', $fieldrequired = 0, $notabletag = 0, $paramid = 'id', $help = '')
    {
		global $conf, $langs;

		$ret = '';

		  
		if (!empty($conf->global->MAIN_USE_JQUERY_JEDITABLE) && !preg_match('/^select;/', $typeofdata))
		{
			if (!empty($perm))
			{
				$tmp = explode(':', $typeofdata);
				$ret .= '<div class="editkey_'.$tmp[0].(!empty($tmp[1]) ? ' '.$tmp[1] : '').'" id="'.$htmlname.'">';
				if ($fieldrequired) $ret .= '<span class="fieldrequired">';
				if ($help) {
					$ret .= $this->textwithpicto($langs->trans($text), $help);
				} else {
					$ret .= $langs->trans($text);
				}
				if ($fieldrequired) $ret .= '</span>';
				$ret .= '</div>'."\n";
			}
			else
			{
				if ($fieldrequired) $ret .= '<span class="fieldrequired">';
				if ($help) {
					$ret .= $this->textwithpicto($langs->trans($text), $help);
				} else {
					$ret .= $langs->trans($text);
				}
				if ($fieldrequired) $ret .= '</span>';
			}
		}
		else
		{
			if (empty($notabletag) && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '<table class="nobordernopadding centpercent"><tr><td class="nowrap">';
			if ($fieldrequired) $ret .= '<span class="fieldrequired">';
			if ($help) {
				$ret .= $this->textwithpicto($langs->trans($text), $help);
			} else {
				$ret .= $langs->trans($text);
			}
			if ($fieldrequired) $ret .= '</span>';
			if (!empty($notabletag)) $ret .= ' ';
			if (empty($notabletag) && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '</td>';
			if (empty($notabletag) && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '<td class="right">';
			if ($htmlname && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '<a class="editfielda" href="'.$_SERVER["PHP_SELF"].'?action=edit'.$htmlname.'&amp;'.$paramid.'='.$object->id.$moreparam.'">'.img_edit($langs->trans('Edit'), ($notabletag ? 0 : 1)).'</a>';
			if (!empty($notabletag) && $notabletag == 1) $ret .= ' : ';
			if (!empty($notabletag) && $notabletag == 3) $ret .= ' ';
			if (empty($notabletag) && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '</td>';
			if (empty($notabletag) && GETPOST('action', 'aZ09') != 'edit'.$htmlname && $perm) $ret .= '</tr></table>';
		}

		return $ret;
    }

	
    public function editfieldval($text, $htmlname, $value, $object, $perm, $typeofdata = 'string', $editvalue = '', $extObject = null, $custommsg = null, $moreparam = '', $notabletag = 0, $formatfunc = '', $paramid = 'id')
	{
		global $conf, $langs, $db;

		$ret = '';

		  
		if (empty($typeofdata)) return 'ErrorBadParameter';

		  
		if (!empty($conf->global->MAIN_USE_JQUERY_JEDITABLE) && !preg_match('/^select;|datehourpicker/', $typeofdata))   
		{
			$ret .= $this->editInPlace($object, $value, $htmlname, $perm, $typeofdata, $editvalue, $extObject, $custommsg);
		}
		else
		{
			if (GETPOST('action', 'aZ09') == 'edit'.$htmlname)
			{
				$ret .= "\n";
				$ret .= '<form method="post" action="'.$_SERVER["PHP_SELF"].($moreparam ? '?'.$moreparam : '').'">';
				$ret .= '<input type="hidden" name="action" value="set'.$htmlname.'">';
				$ret .= '<input type="hidden" name="token" value="'.newToken().'">';
				$ret .= '<input type="hidden" name="'.$paramid.'" value="'.$object->id.'">';
				if (empty($notabletag)) $ret .= '<table class="nobordernopadding centpercent" cellpadding="0" cellspacing="0">';
				if (empty($notabletag)) $ret .= '<tr><td>';
				if (preg_match('/^(string|safehtmlstring|email)/', $typeofdata))
				{
					$tmp = explode(':', $typeofdata);
					$ret .= '<input type="text" id="'.$htmlname.'" name="'.$htmlname.'" value="'.($editvalue ? $editvalue : $value).'"'.($tmp[1] ? ' size="'.$tmp[1].'"' : '').'>';
				}
				elseif (preg_match('/^(numeric|amount)/', $typeofdata))
				{
					$tmp = explode(':', $typeofdata);
					$valuetoshow = price2num($editvalue ? $editvalue : $value);
					$ret .= '<input type="text" id="'.$htmlname.'" name="'.$htmlname.'" value="'.($valuetoshow != '' ?price($valuetoshow) : '').'"'.($tmp[1] ? ' size="'.$tmp[1].'"' : '').'>';
				}
				elseif (preg_match('/^text/', $typeofdata) || preg_match('/^note/', $typeofdata))	  
				{
					$tmp = explode(':', $typeofdata);
					$cols = $tmp[2];
					$morealt = '';
					if (preg_match('/%/', $cols))
					{
						$morealt = ' style="width: '.$cols.'"';
						$cols = '';
					}

					$valuetoshow = ($editvalue ? $editvalue : $value);
					$ret .= '<textarea id="'.$htmlname.'" name="'.$htmlname.'" wrap="soft" rows="'.($tmp[1] ? $tmp[1] : '20').'"'.($cols ? ' cols="'.$cols.'"' : 'class="quatrevingtpercent"').$morealt.'">';
					  
					  
					$valuetoshow = str_replace('&', '&amp;', $valuetoshow);
					$ret .= dol_string_neverthesehtmltags($valuetoshow, array('textarea'));
					$ret .= '</textarea>';
				}
				elseif ($typeofdata == 'day' || $typeofdata == 'datepicker')
				{
					$ret .= $this->selectDate($value, $htmlname, 0, 0, 1, 'form'.$htmlname, 1, 0);
				}
				elseif ($typeofdata == 'dayhour' || $typeofdata == 'datehourpicker')
				{
					$ret .= $this->selectDate($value, $htmlname, 1, 1, 1, 'form'.$htmlname, 1, 0);
				}
				elseif (preg_match('/^select;/', $typeofdata))
				{
					$arraydata = explode(',', preg_replace('/^select;/', '', $typeofdata));
                    $arraylist = array();
					foreach ($arraydata as $val)
					{
						$tmp = explode(':', $val);
						$tmpkey = str_replace('|', ':', $tmp[0]);
						$arraylist[$tmpkey] = $tmp[1];
					}
					$ret .= $this->selectarray($htmlname, $arraylist, $value);
				}
				elseif (preg_match('/^ckeditor/', $typeofdata))
				{
				    $tmp = explode(':', $typeofdata);   
					require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
					$doleditor = new DolEditor($htmlname, ($editvalue ? $editvalue : $value), ($tmp[2] ? $tmp[2] : ''), ($tmp[3] ? $tmp[3] : '100'), ($tmp[1] ? $tmp[1] : 'dolibarr_notes'), 'In', ($tmp[5] ? $tmp[5] : 0), (isset($tmp[8]) ? ($tmp[8] ?true:false) : true), true, ($tmp[6] ? $tmp[6] : '20'), ($tmp[7] ? $tmp[7] : '100'));
					$ret .= $doleditor->Create(1);
				}
				if (empty($notabletag)) $ret .= '</td>';

				if (empty($notabletag)) $ret .= '<td class="left">';
				  
			   	$ret .= '<input type="submit" class="button'.(empty($notabletag) ? '' : ' ').'" name="modify" value="'.$langs->trans("Modify").'">';
			   	if (preg_match('/ckeditor|textarea/', $typeofdata) && empty($notabletag)) $ret .= '<br>'."\n";
			   	$ret .= '<input type="submit" class="button'.(empty($notabletag) ? '' : ' ').'" name="cancel" value="'.$langs->trans("Cancel").'">';
			   	if (empty($notabletag)) $ret .= '</td>';

			   	if (empty($notabletag)) $ret .= '</tr></table>'."\n";
				$ret .= '</form>'."\n";
			}
			else
			{
				if (preg_match('/^(email)/', $typeofdata))              $ret .= dol_print_email($value, 0, 0, 0, 0, 1);
				elseif (preg_match('/^(amount|numeric)/', $typeofdata)) $ret .= ($value != '' ? price($value, '', $langs, 0, -1, -1, $conf->currency) : '');
				elseif (preg_match('/^text/', $typeofdata) || preg_match('/^note/', $typeofdata))  $ret .= dol_htmlentitiesbr($value);
				elseif (preg_match('/^safehtmlstring/', $typeofdata)) $ret .= dol_string_onlythesehtmltags($value);
				elseif ($typeofdata == 'day' || $typeofdata == 'datepicker') $ret .= dol_print_date($value, 'day');
				elseif ($typeofdata == 'dayhour' || $typeofdata == 'datehourpicker') $ret .= dol_print_date($value, 'dayhour');
				elseif (preg_match('/^select;/', $typeofdata))
				{
					$arraydata = explode(',', preg_replace('/^select;/', '', $typeofdata));
                    $arraylist = array();
					foreach ($arraydata as $val)
					{
						$tmp = explode(':', $val);
						$arraylist[$tmp[0]] = $tmp[1];
					}
					$ret .= $arraylist[$value];
				}
				elseif (preg_match('/^ckeditor/', $typeofdata))
				{
					$tmpcontent = dol_htmlentitiesbr($value);
					if (!empty($conf->global->MAIN_DISABLE_NOTES_TAB))
					{
						$firstline = preg_replace('/<br>.*/', '', $tmpcontent);
						$firstline = preg_replace('/[\n\r].*/', '', $firstline);
						$tmpcontent = $firstline.((strlen($firstline) != strlen($tmpcontent)) ? '...' : '');
					}
					  
					  
					$ret .= dol_string_onlythesehtmltags(dol_htmlentitiesbr($tmpcontent));
				}
				else {
					$ret .= dol_escape_htmltag($value);
				}

				if ($formatfunc && method_exists($object, $formatfunc))
				{
					$ret = $object->$formatfunc($ret);
				}
			}
		}
		return $ret;
	}

	
	protected function editInPlace($object, $value, $htmlname, $condition, $inputType = 'textarea', $editvalue = null, $extObject = null, $custommsg = null)
	{
		global $conf;

		$out = '';

		  
		if (preg_match('/^text/', $inputType)) $value = dol_nl2br($value);
		elseif (preg_match('/^numeric/', $inputType)) $value = price($value);
		elseif ($inputType == 'day' || $inputType == 'datepicker') $value = dol_print_date($value, 'day');

		if ($condition)
		{
			$element = false;
			$table_element = false;
			$fk_element		= false;
			$loadmethod		= false;
			$savemethod		= false;
			$ext_element	= false;
			$button_only	= false;
			$inputOption = '';

			if (is_object($object))
			{
				$element = $object->element;
				$table_element = $object->table_element;
				$fk_element = $object->id;
			}

			if (is_object($extObject))
			{
				$ext_element = $extObject->element;
			}

			if (preg_match('/^(string|email|numeric)/', $inputType))
			{
				$tmp = explode(':', $inputType);
				$inputType = $tmp[0];
				if (!empty($tmp[1])) $inputOption = $tmp[1];
				if (!empty($tmp[2])) $savemethod = $tmp[2];
				$out .= '<input id="width_'.$htmlname.'" value="'.$inputOption.'" type="hidden"/>'."\n";
			}
			elseif ((preg_match('/^day$/', $inputType)) || (preg_match('/^datepicker/', $inputType)) || (preg_match('/^datehourpicker/', $inputType)))
			{
				$tmp = explode(':', $inputType);
				$inputType = $tmp[0];
				if (!empty($tmp[1])) $inputOption = $tmp[1];
				if (!empty($tmp[2])) $savemethod = $tmp[2];

				$out .= '<input id="timestamp" type="hidden"/>'."\n";   
			}
			elseif (preg_match('/^(select|autocomplete)/', $inputType))
			{
				$tmp = explode(':', $inputType);
				$inputType = $tmp[0]; $loadmethod = $tmp[1];
				if (!empty($tmp[2])) $savemethod = $tmp[2];
				if (!empty($tmp[3])) $button_only = true;
			}
			elseif (preg_match('/^textarea/', $inputType))
			{
				$tmp = explode(':', $inputType);
				$inputType = $tmp[0];
				$rows = (empty($tmp[1]) ? '8' : $tmp[1]);
				$cols = (empty($tmp[2]) ? '80' : $tmp[2]);
			}
			elseif (preg_match('/^ckeditor/', $inputType))
			{
				$tmp = explode(':', $inputType);
				$inputType = $tmp[0]; $toolbar = $tmp[1];
				if (!empty($tmp[2])) $width = $tmp[2];
				if (!empty($tmp[3])) $heigth = $tmp[3];
				if (!empty($tmp[4])) $savemethod = $tmp[4];

				if (!empty($conf->fckeditor->enabled))
				{
					$out .= '<input id="ckeditor_toolbar" value="'.$toolbar.'" type="hidden"/>'."\n";
				}
				else
				{
					$inputType = 'textarea';
				}
			}

			$out .= '<input id="element_'.$htmlname.'" value="'.$element.'" type="hidden"/>'."\n";
			$out .= '<input id="table_element_'.$htmlname.'" value="'.$table_element.'" type="hidden"/>'."\n";
			$out .= '<input id="fk_element_'.$htmlname.'" value="'.$fk_element.'" type="hidden"/>'."\n";
			$out .= '<input id="loadmethod_'.$htmlname.'" value="'.$loadmethod.'" type="hidden"/>'."\n";
			if (!empty($savemethod))	$out .= '<input id="savemethod_'.$htmlname.'" value="'.$savemethod.'" type="hidden"/>'."\n";
			if (!empty($ext_element))	$out .= '<input id="ext_element_'.$htmlname.'" value="'.$ext_element.'" type="hidden"/>'."\n";
			if (!empty($custommsg))
			{
				if (is_array($custommsg))
				{
					if (!empty($custommsg['success']))
						$out .= '<input id="successmsg_'.$htmlname.'" value="'.$custommsg['success'].'" type="hidden"/>'."\n";
					if (!empty($custommsg['error']))
						$out .= '<input id="errormsg_'.$htmlname.'" value="'.$custommsg['error'].'" type="hidden"/>'."\n";
				}
				else
					$out .= '<input id="successmsg_'.$htmlname.'" value="'.$custommsg.'" type="hidden"/>'."\n";
			}
			if ($inputType == 'textarea') {
				$out .= '<input id="textarea_'.$htmlname.'_rows" value="'.$rows.'" type="hidden"/>'."\n";
				$out .= '<input id="textarea_'.$htmlname.'_cols" value="'.$cols.'" type="hidden"/>'."\n";
			}
			$out .= '<span id="viewval_'.$htmlname.'" class="viewval_'.$inputType.($button_only ? ' inactive' : ' active').'">'.$value.'</span>'."\n";
			$out .= '<span id="editval_'.$htmlname.'" class="editval_'.$inputType.($button_only ? ' inactive' : ' active').' hideobject">'.(!empty($editvalue) ? $editvalue : $value).'</span>'."\n";
		}
		else
		{
			$out = $value;
		}

		return $out;
	}

	
    public function textwithtooltip($text, $htmltext, $tooltipon = 1, $direction = 0, $img = '', $extracss = '', $notabs = 3, $incbefore = '', $noencodehtmltext = 0, $tooltiptrigger = '', $forcenowrap = 0)
	{
		global $conf;

		if ($incbefore) $text = $incbefore.$text;
		if (!$htmltext) return $text;

		$tag = 'td';
		if ($notabs == 2) $tag = 'div';
		if ($notabs == 3) $tag = 'span';
		  
		  
		$htmltext = str_replace("\r", "", $htmltext);
		$htmltext = str_replace("\n", "", $htmltext);

		$extrastyle = '';
		if ($direction < 0) { $extracss = ($extracss ? $extracss.' ' : '').($notabs != 3 ? 'inline-block' : ''); $extrastyle = 'padding: 0px; padding-left: 3px !important;'; }
		if ($direction > 0) { $extracss = ($extracss ? $extracss.' ' : '').($notabs != 3 ? 'inline-block' : ''); $extrastyle = 'padding: 0px; padding-right: 3px !important;'; }

		$classfortooltip = 'classfortooltip';

		$s = ''; $textfordialog = '';

		if ($tooltiptrigger == '')
		{
			$htmltext = str_replace('"', "&quot;", $htmltext);
		}
		else
		{
			$classfortooltip = 'classfortooltiponclick';
			$textfordialog .= '<div style="display: none;" id="idfortooltiponclick_'.$tooltiptrigger.'" class="classfortooltiponclicktext">'.$htmltext.'</div>';
		}
		if ($tooltipon == 2 || $tooltipon == 3)
		{
			$paramfortooltipimg = ' class="'.$classfortooltip.($notabs != 3 ? ' inline-block' : '').($extracss ? ' '.$extracss : '').'" style="padding: 0px;'.($extrastyle ? ' '.$extrastyle : '').'"';
			if ($tooltiptrigger == '') $paramfortooltipimg .= ' title="'.($noencodehtmltext ? $htmltext : dol_escape_htmltag($htmltext, 1)).'"';   
			else $paramfortooltipimg .= ' dolid="'.$tooltiptrigger.'"';
		}
		else $paramfortooltipimg = ($extracss ? ' class="'.$extracss.'"' : '').($extrastyle ? ' style="'.$extrastyle.'"' : '');   
		if ($tooltipon == 1 || $tooltipon == 3)
		{
			$paramfortooltiptd = ' class="'.($tooltipon == 3 ? 'cursorpointer ' : '').$classfortooltip.' inline-block'.($extracss ? ' '.$extracss : '').'" style="padding: 0px;'.($extrastyle ? ' '.$extrastyle : '').'" ';
			if ($tooltiptrigger == '') $paramfortooltiptd .= ' title="'.($noencodehtmltext ? $htmltext : dol_escape_htmltag($htmltext, 1)).'"';   
			else $paramfortooltiptd .= ' dolid="'.$tooltiptrigger.'"';
		}
		else $paramfortooltiptd = ($extracss ? ' class="'.$extracss.'"' : '').($extrastyle ? ' style="'.$extrastyle.'"' : '');   
		if (empty($notabs)) $s .= '<table class="nobordernopadding"><tr style="height: auto;">';
		elseif ($notabs == 2) $s .= '<div class="inline-block'.($forcenowrap ? ' nowrap' : '').'">';
		  
		if ($direction < 0) {
			$s .= '<'.$tag.$paramfortooltipimg;
			if ($tag == 'td') {
				$s .= ' class=valigntop" width="14"';
			}
			$s .= '>'.$textfordialog.$img.'</'.$tag.'>';
		}
		  
		  
		if ((string) $text != '') $s .= '<'.$tag.$paramfortooltiptd.'>'.$text.'</'.$tag.'>';
		  
		if ($direction > 0) {
			$s .= '<'.$tag.$paramfortooltipimg;
			if ($tag == 'td') $s .= ' class="valignmiddle" width="14"';
			$s .= '>'.$textfordialog.$img.'</'.$tag.'>';
		}
		if (empty($notabs)) $s .= '</tr></table>';
		elseif ($notabs == 2) $s .= '</div>';

		return $s;
	}

	
    public function textwithpicto($text, $htmltext, $direction = 1, $type = 'help', $extracss = '', $noencodehtmltext = 0, $notabs = 3, $tooltiptrigger = '', $forcenowrap = 0)
	{
		global $conf, $langs;

		$alt = '';
		if ($tooltiptrigger) $alt = $langs->transnoentitiesnoconv("ClickToShowHelp");

		  
		if ($type == '0') $type = 'info';
		elseif ($type == '1') $type = 'help';

		  
		if (empty($conf->use_javascript_ajax))
		{
			if ($type == 'info' || $type == 'infoclickable' || $type == 'help' || $type == 'helpclickable')	return $text;
			else
			{
				$alt = $htmltext;
				$htmltext = '';
			}
		}

		  
		if (!empty($conf->dol_no_mouse_hover) && empty($tooltiptrigger))
		{
			if ($type == 'info' || $type == 'infoclickable' || $type == 'help' || $type == 'helpclickable') return $text;
		}
		  
		  
		  
			  
		  

		$img = '';
		if ($type == 'info') $img = img_help(0, $alt);
		elseif ($type == 'help') $img = img_help(($tooltiptrigger != '' ? 2 : 1), $alt);
		elseif ($type == 'helpclickable') $img = img_help(($tooltiptrigger != '' ? 2 : 1), $alt);
		elseif ($type == 'superadmin') $img = img_picto($alt, 'redstar');
		elseif ($type == 'admin') $img = img_picto($alt, 'star');
		elseif ($type == 'warning') $img = img_warning($alt);
		elseif ($type != 'none') $img = img_picto($alt, $type);   

		return $this->textwithtooltip($text, $htmltext, ((($tooltiptrigger && !$img) || strpos($type, 'clickable')) ? 3 : 2), $direction, $img, $extracss, $notabs, '', $noencodehtmltext, $tooltiptrigger, $forcenowrap);
	}

	
    public function selectMassAction($selected, $arrayofaction, $alwaysvisible = 0)
	{
		global $conf, $langs, $hookmanager;


		$disabled = 0;
		$ret = '<div class="centpercent center">';
		$ret .= '<select class="flat'.(empty($conf->use_javascript_ajax) ? '' : ' hideobject').' massaction massactionselect valignmiddle" name="massaction"'.($disabled ? ' disabled="disabled"' : '').'>';

		  
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreMassActions', $parameters);   
		  
		if (count($arrayofaction) == 0 && empty($hookmanager->resPrint)) return;
		if (empty($reshook))
		{
			$ret .= '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>-- '.$langs->trans("SelectAction").' --</option>';
			foreach ($arrayofaction as $code => $label)
			{
				$ret .= '<option value="'.$code.'"'.($disabled ? ' disabled="disabled"' : '').' data-html="'.dol_escape_htmltag($label).'">'.$label.'</option>';
			}
		}
		$ret .= $hookmanager->resPrint;

		$ret .= '</select>';

		if (empty($conf->dol_optimize_smallscreen)) $ret .= ajax_combobox('.massactionselect');

		  
		$ret .= '<input type="submit" name="confirmmassactioninvisible" style="display: none" tabindex="-1">';   
		$ret .= '<input type="submit" disabled name="confirmmassaction" class="button'.(empty($conf->use_javascript_ajax) ? '' : ' hideobject').' massaction massactionconfirmed" value="'.dol_escape_htmltag($langs->trans("Confirm")).'">';
		$ret .= '</div>';

		if (!empty($conf->use_javascript_ajax))
		{
			$ret .= '<!-- JS CODE TO ENABLE mass action select -->
    		<script>
        		function initCheckForSelect(mode)	/* mode is 0 during init of page or click all, 1 when we click on 1 checkbox */
        		{
        			atleastoneselected=0;
    	    		jQuery(".checkforselect").each(function( index ) {
    	  				/* console.log( index + ": " + $( this ).text() ); */
    	  				if ($(this).is(\':checked\')) atleastoneselected++;
    	  			});

					console.log("initCheckForSelect mode="+mode+" atleastoneselected="+atleastoneselected);

    	  			if (atleastoneselected || '.$alwaysvisible.')
    	  			{
    	  				jQuery(".massaction").show();
        			    '.($selected ? 'if (atleastoneselected) { jQuery(".massactionselect").val("'.$selected.'").trigger(\'change\'); jQuery(".massactionconfirmed").prop(\'disabled\', false); }' : '').'
        			    '.($selected ? 'if (! atleastoneselected) { jQuery(".massactionselect").val("0").trigger(\'change\'); jQuery(".massactionconfirmed").prop(\'disabled\', true); } ' : '').'
    	  			}
    	  			else
    	  			{
    	  				jQuery(".massaction").hide();
    	            }
        		}

        	jQuery(document).ready(function () {
        		initCheckForSelect(0);
        		jQuery(".checkforselect").click(function() {
        			initCheckForSelect(1);
    	  		});
    	  		jQuery(".massactionselect").change(function() {
        			var massaction = $( this ).val();
        			var urlform = $( this ).closest("form").attr("action").replace("#show_files","");
        			if (massaction == "builddoc")
                    {
                        urlform = urlform + "#show_files";
    	            }
        			$( this ).closest("form").attr("action", urlform);
                    console.log("we select a mass action "+massaction+" - "+urlform);
        	        /* Warning: if you set submit button to disabled, post using Enter will no more work if there is no other button */
        			if ($(this).val() != \'0\')
    	  			{
    	  				jQuery(".massactionconfirmed").prop(\'disabled\', false);
    	  			}
    	  			else
    	  			{
    	  				jQuery(".massactionconfirmed").prop(\'disabled\', true);
    	  			}
    	        });
        	});
    		</script>
        	';
		}

		return $ret;
	}

      
	
    public function select_country($selected = '', $htmlname = 'country_id', $htmloption = '', $maxlength = 0, $morecss = 'minwidth300', $usecodeaskey = '', $showempty = 1, $disablefavorites = 0, $addspecialentries = 0)
	{
          
		global $conf, $langs, $mysoc;

		$langs->load("dict");

		$out = '';
		$countryArray = array();
		$favorite = array();
		$label = array();
		$atleastonefavorite = 0;

		$sql = "SELECT rowid, code as code_iso, code_iso as code_iso3, label, favorite";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_country";
		$sql .= " WHERE active > 0";
		  

		dol_syslog(get_class($this)."::select_country", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$out .= '<select id="select'.$htmlname.'" class="flat maxwidth200onsmartphone selectcountry'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'" '.$htmloption.'>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				$foundselected = false;

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$countryArray[$i]['rowid'] = $obj->rowid;
					$countryArray[$i]['code_iso'] = $obj->code_iso;
					$countryArray[$i]['code_iso3'] 	= $obj->code_iso3;
					$countryArray[$i]['label'] = ($obj->code_iso && $langs->transnoentitiesnoconv("Country".$obj->code_iso) != "Country".$obj->code_iso ? $langs->transnoentitiesnoconv("Country".$obj->code_iso) : ($obj->label != '-' ? $obj->label : ''));
					$countryArray[$i]['favorite']   = $obj->favorite;
					$favorite[$i] = $obj->favorite;
					$label[$i] = dol_string_unaccent($countryArray[$i]['label']);
					$i++;
				}

				if (empty($disablefavorites)) array_multisort($favorite, SORT_DESC, $label, SORT_ASC, $countryArray);
				else $countryArray = dol_sort_array($countryArray, 'label');

				if ($showempty)
				{
					$out .= '<option value="">&nbsp;</option>'."\n";
				}

				if ($addspecialentries)	  
				{
					  
					$out .= '<option value="special_allnotme"'.($selected == 'special_allnotme' ? ' selected' : '').'>'.$langs->trans("CountriesExceptMe", $langs->transnoentitiesnoconv("Country".$mysoc->country_code)).'</option>';
					$out .= '<option value="special_eec"'.($selected == 'special_eec' ? ' selected' : '').'>'.$langs->trans("CountriesInEEC").'</option>';
					if ($mysoc->isInEEC()) $out .= '<option value="special_eecnotme"'.($selected == 'special_eecnotme' ? ' selected' : '').'>'.$langs->trans("CountriesInEECExceptMe", $langs->transnoentitiesnoconv("Country".$mysoc->country_code)).'</option>';
					$out .= '<option value="special_noteec"'.($selected == 'special_noteec' ? ' selected' : '').'>'.$langs->trans("CountriesNotInEEC").'</option>';
					$out .= '<option value="" disabled class="selectoptiondisabledwhite">--------------</option>';
				}

				foreach ($countryArray as $row)
				{
					  
					if (empty($row['rowid'])) continue;

					if (empty($disablefavorites) && $row['favorite'] && $row['code_iso']) $atleastonefavorite++;
					if (empty($row['favorite']) && $atleastonefavorite)
					{
						$atleastonefavorite = 0;
						$out .= '<option value="" disabled class="selectoptiondisabledwhite">--------------</option>';
					}
					if ($selected && $selected != '-1' && ($selected == $row['rowid'] || $selected == $row['code_iso'] || $selected == $row['code_iso3'] || $selected == $row['label']))
					{
						$foundselected = true;
						$out .= '<option value="'.($usecodeaskey ? ($usecodeaskey == 'code2' ? $row['code_iso'] : $row['code_iso3']) : $row['rowid']).'" selected>';
					}
					else
					{
						$out .= '<option value="'.($usecodeaskey ? ($usecodeaskey == 'code2' ? $row['code_iso'] : $row['code_iso3']) : $row['rowid']).'">';
					}
					if ($row['label']) $out .= dol_trunc($row['label'], $maxlength, 'middle');
					else $out .= '&nbsp;';
					if ($row['code_iso']) $out .= ' ('.$row['code_iso'].')';
					$out .= '</option>';
				}
			}
			$out .= '</select>';
		}
		else
		{
			dol_print_error($this->db);
		}

		  
		include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
		$out .= ajax_combobox('select'.$htmlname);

		return $out;
	}

      
	
    public function select_incoterms($selected = '', $location_incoterms = '', $page = '', $htmlname = 'incoterm_id', $htmloption = '', $forcecombo = 1, $events = array())
	{
          
		global $conf, $langs;

		$langs->load("dict");

		$out = '';
		$incotermArray = array();

		$sql = "SELECT rowid, code";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_incoterms";
		$sql .= " WHERE active > 0";
		$sql .= " ORDER BY code ASC";

		dol_syslog(get_class($this)."::select_incoterm", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			if ($conf->use_javascript_ajax && !$forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, $events);
			}

			if (!empty($page))
			{
				$out .= '<form method="post" action="'.$page.'">';
				$out .= '<input type="hidden" name="action" value="set_incoterms">';
				$out .= '<input type="hidden" name="token" value="'.newToken().'">';
			}

			$out .= '<select id="'.$htmlname.'" class="flat selectincoterm minwidth100imp noenlargeonsmartphone" name="'.$htmlname.'" '.$htmloption.'>';
			$out .= '<option value="0">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				$foundselected = false;

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$incotermArray[$i]['rowid'] = $obj->rowid;
					$incotermArray[$i]['code'] = $obj->code;
					$i++;
				}

				foreach ($incotermArray as $row)
				{
					if ($selected && ($selected == $row['rowid'] || $selected == $row['code']))
					{
						$out .= '<option value="'.$row['rowid'].'" selected>';
					}
					else
					{
						$out .= '<option value="'.$row['rowid'].'">';
					}

					if ($row['code']) $out .= $row['code'];

					$out .= '</option>';
				}
			}
			$out .= '</select>';

			$out .= '<input id="location_incoterms" class="maxwidth100onsmartphone" name="location_incoterms" value="'.$location_incoterms.'">';

			if (!empty($page))
			{
				$out .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'"></form>';
			}
		}
		else
		{
			dol_print_error($this->db);
		}

		return $out;
	}

      
	
    public function select_type_of_lines($selected = '', $htmlname = 'type', $showempty = 0, $hidetext = 0, $forceall = 0)
	{
          
		global $db, $langs, $user, $conf;

		  
		if ($forceall == 1 || (empty($forceall) && !empty($conf->product->enabled) && !empty($conf->service->enabled))
		|| (empty($forceall) && empty($conf->product->enabled) && empty($conf->service->enabled)))
		{
			if (empty($hidetext)) print $langs->trans("Type").': ';
			print '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';
			if ($showempty)
			{
				print '<option value="-1"';
				if ($selected == -1) print ' selected';
				print '>&nbsp;</option>';
			}

			print '<option value="0"';
			if (0 == $selected) print ' selected';
			print '>'.$langs->trans("Product");

			print '<option value="1"';
			if (1 == $selected) print ' selected';
			print '>'.$langs->trans("Service");

			print '</select>';
			  
		}
		if ((empty($forceall) && empty($conf->product->enabled) && !empty($conf->service->enabled)) || $forceall == 3)
		{
			print $langs->trans("Service");
			print '<input type="hidden" name="'.$htmlname.'" value="1">';
		}
		if ((empty($forceall) && !empty($conf->product->enabled) && empty($conf->service->enabled)) || $forceall == 2)
		{
			print $langs->trans("Product");
			print '<input type="hidden" name="'.$htmlname.'" value="0">';
		}
		if ($forceall < 0)	  
		{
			print '<input type="hidden" name="'.$htmlname.'" value="1">';   
		}
	}

      
	
    public function load_cache_types_fees()
	{
          
		global $langs;

		$num = count($this->cache_types_fees);
		if ($num > 0) return 0;   

		dol_syslog(__METHOD__, LOG_DEBUG);

		$langs->load("trips");

		$sql = "SELECT c.code, c.label";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_type_fees as c";
		$sql .= " WHERE active > 0";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				  
				$label = ($obj->code != $langs->trans($obj->code) ? $langs->trans($obj->code) : $langs->trans($obj->label));
				$this->cache_types_fees[$obj->code] = $label;
				$i++;
			}

			asort($this->cache_types_fees);

			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

      
	
    public function select_type_fees($selected = '', $htmlname = 'type', $showempty = 0)
	{
          
		global $user, $langs;

		dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

		$this->load_cache_types_fees();

		print '<select id="select_'.$htmlname.'" class="flat" name="'.$htmlname.'">';
		if ($showempty)
		{
			print '<option value="-1"';
			if ($selected == -1) print ' selected';
			print '>&nbsp;</option>';
		}

		foreach ($this->cache_types_fees as $key => $value)
		{
			print '<option value="'.$key.'"';
			if ($key == $selected) print ' selected';
			print '>';
			print $value;
			print '</option>';
		}

		print '</select>';
		if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}


      
	
    public function select_thirdparty($selected = '', $htmlname = 'socid', $filter = '', $limit = 20, $ajaxoptions = array(), $forcecombo = 0)
	{
          
   		return $this->select_thirdparty_list($selected, $htmlname, $filter, 1, 0, $forcecombo, array(), '', 0, $limit);
	}

      
	
    public function select_company($selected = '', $htmlname = 'socid', $filter = '', $showempty = '', $showtype = 0, $forcecombo = 0, $events = array(), $limit = 0, $morecss = 'minwidth100', $moreparam = '', $selected_input_value = '', $hidelabel = 1, $ajaxoptions = array(), $multiple = false)
	{
          
		global $conf, $user, $langs;

		$out = '';

		if (!empty($conf->use_javascript_ajax) && !empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && !$forcecombo)
		{
			  
			$placeholder = '';
			if ($selected && empty($selected_input_value))
			{
				require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
				$societetmp = new Societe($this->db);
				$societetmp->fetch($selected);
				$selected_input_value = $societetmp->name;
				unset($societetmp);
			}
			  
			$urloption = 'htmlname='.urlencode($htmlname).'&outjson=1&filter='.urlencode($filter).($showtype ? '&showtype='.urlencode($showtype) : '');
			$out .= ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/societe/ajax/company.php', $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out .= '<style type="text/css">.ui-autocomplete { z-index: 250; }</style>';
			if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
			elseif ($hidelabel > 1) {
				$placeholder = ' placeholder="'.$langs->trans("RefOrLabel").'"';
				if ($hidelabel == 2) {
					$out .= img_picto($langs->trans("Search"), 'search');
				}
			}
			$out .= '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->THIRDPARTY_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
			if ($hidelabel == 3) {
				$out .= img_picto($langs->trans("Search"), 'search');
			}
		}
		else
		{
			  
			$out .= $this->select_thirdparty_list($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam, $multiple);
		}

		return $out;
	}

      
	
    public function select_thirdparty_list($selected = '', $htmlname = 'socid', $filter = '', $showempty = '', $showtype = 0, $forcecombo = 0, $events = array(), $filterkey = '', $outputmode = 0, $limit = 0, $morecss = 'minwidth100', $moreparam = '', $multiple = false)
	{
          
		global $conf, $user, $langs;

		$out = '';
		$num = 0;
		$outarray = array();

		if ($selected === '') $selected = array();
		elseif (!is_array($selected)) $selected = array($selected);

		  
		if (function_exists('testSqlAndScriptInject')) {
			if (testSqlAndScriptInject($filter, 3) > 0) {
				$filter = '';
			}
		}

		  
		$sql = "SELECT s.rowid, s.nom as name, s.name_alias, s.client, s.fournisseur, s.code_client, s.code_fournisseur";

		if ($conf->global->COMPANY_SHOW_ADDRESS_SELECTLIST) {
			$sql .= ", s.address, s.zip, s.town";
		 	$sql .= ", dictp.code as country_code";
		}

		$sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
		if (!$user->rights->societe->client->voir && !$user->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		if ($conf->global->COMPANY_SHOW_ADDRESS_SELECTLIST) {
			$sql .= " LEFT OUTER JOIN ".MAIN_DB_PREFIX."c_country as dictp ON dictp.rowid=s.fk_pays";
		}
		$sql .= " WHERE s.entity IN (".getEntity('societe').")";
		if (!empty($user->socid)) $sql .= " AND s.rowid = ".$user->socid;
		if ($filter) $sql .= " AND (".$filter.")";
		if (!$user->rights->societe->client->voir && !$user->socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = ".$user->id;
		if (!empty($conf->global->COMPANY_HIDE_INACTIVE_IN_COMBOBOX)) $sql .= " AND s.status <> 0";
		  
		if ($filterkey && $filterkey != '')
		{
			$sql .= " AND (";
			$prefix = empty($conf->global->COMPANY_DONOTSEARCH_ANYWHERE) ? '%' : '';   
			  
			$scrit = explode(' ', $filterkey);
			$i = 0;
			if (count($scrit) > 1) $sql .= "(";
			foreach ($scrit as $crit) {
				if ($i > 0) $sql .= " AND ";
				$sql .= "(s.nom LIKE '".$this->db->escape($prefix.$crit)."%')";
				$i++;
			}
			if (count($scrit) > 1) $sql .= ")";
			if (!empty($conf->barcode->enabled))
			{
				$sql .= " OR s.barcode LIKE '".$this->db->escape($prefix.$filterkey)."%'";
			}
			$sql .= " OR s.code_client LIKE '".$this->db->escape($prefix.$filterkey)."%' OR s.code_fournisseur LIKE '".$this->db->escape($prefix.$filterkey)."%'";
			$sql .= ")";
		}
		$sql .= $this->db->order("nom", "ASC");
		$sql .= $this->db->plimit($limit, 0);

		  
		dol_syslog(get_class($this)."::select_thirdparty_list", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
		   	if (!$forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
			}

			  
			$out .= '<select id="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($moreparam ? ' '.$moreparam : '').' name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').'>'."\n";

			$textifempty = '';
			  
			  
			if (!empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))
			{
				if ($showempty && !is_numeric($showempty)) $textifempty = $langs->trans($showempty);
				else $textifempty .= $langs->trans("All");
			}
			if ($showempty) $out .= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$label = '';
					if ($conf->global->SOCIETE_ADD_REF_IN_LIST) {
						if (($obj->client) && (!empty($obj->code_client))) {
							$label = $obj->code_client.' - ';
						}
						if (($obj->fournisseur) && (!empty($obj->code_fournisseur))) {
							$label .= $obj->code_fournisseur.' - ';
						}
						$label .= ' '.$obj->name;
					}
					else
					{
						$label = $obj->name;
					}

					if (!empty($obj->name_alias)) {
						$label .= ' ('.$obj->name_alias.')';
					}

					if ($showtype)
					{
						if ($obj->client || $obj->fournisseur) $label .= ' (';
						if ($obj->client == 1 || $obj->client == 3) $label .= $langs->trans("Customer");
						if ($obj->client == 2 || $obj->client == 3) $label .= ($obj->client == 3 ? ', ' : '').$langs->trans("Prospect");
						if ($obj->fournisseur) $label .= ($obj->client ? ', ' : '').$langs->trans("Supplier");
						if ($obj->client || $obj->fournisseur) $label .= ')';
					}

					if ($conf->global->COMPANY_SHOW_ADDRESS_SELECTLIST) {
						$label .= '-'.$obj->address.'-'.$obj->zip.' '.$obj->town;
						if (!empty($obj->country_code)) {
							$label .= ' '.$langs->trans('Country'.$obj->country_code);
						}
					}

					if (empty($outputmode))
					{
						if (in_array($obj->rowid, $selected))
						{
							$out .= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
						}
						else
						{
							$out .= '<option value="'.$obj->rowid.'">'.$label.'</option>';
						}
					}
					else
					{
						array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));
					}

					$i++;
					if (($i % 10) == 0) $out .= "\n";
				}
			}
			$out .= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		$this->result = array('nbofthirdparties'=>$num);

		if ($outputmode) return $outarray;
		return $out;
	}


      
	
    public function select_remises($selected, $htmlname, $filter, $socid, $maxvalue = 0)
	{
          
		global $langs, $conf;

		  
		$sql = "SELECT re.rowid, re.amount_ht, re.amount_tva, re.amount_ttc,";
		$sql .= " re.description, re.fk_facture_source";
		$sql .= " FROM ".MAIN_DB_PREFIX."societe_remise_except as re";
		$sql .= " WHERE re.fk_soc = ".(int) $socid;
		$sql .= " AND re.entity = ".$conf->entity;
		if ($filter) $sql .= " AND ".$filter;
		$sql .= " ORDER BY re.description ASC";

		dol_syslog(get_class($this)."::select_remises", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			print '<select id="select_'.$htmlname.'" class="flat maxwidthonsmartphone" name="'.$htmlname.'">';
			$num = $this->db->num_rows($resql);

			$qualifiedlines = $num;

			$i = 0;
			if ($num)
			{
				print '<option value="0">&nbsp;</option>';
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$desc = dol_trunc($obj->description, 40);
					if (preg_match('/\(CREDIT_NOTE\)/', $desc)) $desc = preg_replace('/\(CREDIT_NOTE\)/', $langs->trans("CreditNote"), $desc);
					if (preg_match('/\(DEPOSIT\)/', $desc)) $desc = preg_replace('/\(DEPOSIT\)/', $langs->trans("Deposit"), $desc);
					if (preg_match('/\(EXCESS RECEIVED\)/', $desc)) $desc = preg_replace('/\(EXCESS RECEIVED\)/', $langs->trans("ExcessReceived"), $desc);
					if (preg_match('/\(EXCESS PAID\)/', $desc)) $desc = preg_replace('/\(EXCESS PAID\)/', $langs->trans("ExcessPaid"), $desc);

					$selectstring = '';
					if ($selected > 0 && $selected == $obj->rowid) $selectstring = ' selected';

					$disabled = '';
					if ($maxvalue > 0 && $obj->amount_ttc > $maxvalue)
					{
						$qualifiedlines--;
						$disabled = ' disabled';
					}

					if (!empty($conf->global->MAIN_SHOW_FACNUMBER_IN_DISCOUNT_LIST) && !empty($obj->fk_facture_source))
					{
						$tmpfac = new Facture($this->db);
						if ($tmpfac->fetch($obj->fk_facture_source) > 0) $desc = $desc.' - '.$tmpfac->ref;
					}

					print '<option value="'.$obj->rowid.'"'.$selectstring.$disabled.'>'.$desc.' ('.price($obj->amount_ht).' '.$langs->trans("HT").' - '.price($obj->amount_ttc).' '.$langs->trans("TTC").')</option>';
					$i++;
				}
			}
			print '</select>';
			return $qualifiedlines;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

      
	
    public function select_contacts($socid, $selected = '', $htmlname = 'contactid', $showempty = 0, $exclude = '', $limitto = '', $showfunction = 0, $moreclass = '', $showsoc = 0, $forcecombo = 0, $events = array(), $options_only = false, $moreparam = '', $htmlid = '')
    {
          
		print $this->selectcontacts($socid, $selected, $htmlname, $showempty, $exclude, $limitto, $showfunction, $moreclass, $options_only, $showsoc, $forcecombo, $events, $moreparam, $htmlid);
		return $this->num;
    }

	
    public function selectcontacts($socid, $selected = '', $htmlname = 'contactid', $showempty = 0, $exclude = '', $limitto = '', $showfunction = 0, $moreclass = '', $options_only = false, $showsoc = 0, $forcecombo = 0, $events = array(), $moreparam = '', $htmlid = '', $multiple = false)
    {
		global $conf, $langs, $hookmanager, $action;

		$langs->load('companies');

		if (empty($htmlid)) $htmlid = $htmlname;

		if ($selected === '') $selected = array();
		elseif (!is_array($selected)) $selected = array($selected);
		$out = '';

		if (!is_object($hookmanager))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
			$hookmanager = new HookManager($this->db);
		}

		  
		$sql = "SELECT sp.rowid, sp.lastname, sp.statut, sp.firstname, sp.poste";
		if ($showsoc > 0) $sql .= " , s.nom as company";
		$sql .= " FROM ".MAIN_DB_PREFIX."socpeople as sp";
		if ($showsoc > 0) $sql .= " LEFT OUTER JOIN  ".MAIN_DB_PREFIX."societe as s ON s.rowid=sp.fk_soc";
		$sql .= " WHERE sp.entity IN (".getEntity('socpeople').")";
		if ($socid > 0 || $socid == -1) $sql .= " AND sp.fk_soc=".$socid;
		if (!empty($conf->global->CONTACT_HIDE_INACTIVE_IN_COMBOBOX)) $sql .= " AND sp.statut <> 0";
		$sql .= " ORDER BY sp.lastname ASC";

		dol_syslog(get_class($this)."::select_contacts", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);

			if ($conf->use_javascript_ajax && !$forcecombo && !$options_only)
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlid, $events, $conf->global->CONTACT_USE_SEARCH_TO_SELECT);
			}

			if ($htmlname != 'none' && !$options_only) $out .= '<select class="flat'.($moreclass ? ' '.$moreclass : '').'" id="'.$htmlid.'" name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').' '.(!empty($moreparam) ? $moreparam : '').'>';
			if (($showempty == 1 || ($showempty == 3 && $num > 1)) && !$multiple) $out .= '<option value="0"'.(in_array(0, $selected) ? ' selected' : '').'>&nbsp;</option>';
			if ($showempty == 2) $out .= '<option value="0"'.(in_array(0, $selected) ? ' selected' : '').'>'.$langs->trans("Internal").'</option>';

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				include_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
				$contactstatic = new Contact($this->db);

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);

					$contactstatic->id = $obj->rowid;
					$contactstatic->lastname = $obj->lastname;
					$contactstatic->firstname = $obj->firstname;
					if ($obj->statut == 1) {
					    if ($htmlname != 'none')
					    {
						    $disabled = 0;
						    if (is_array($exclude) && count($exclude) && in_array($obj->rowid, $exclude)) $disabled = 1;
						    if (is_array($limitto) && count($limitto) && !in_array($obj->rowid, $limitto)) $disabled = 1;
						    if (!empty($selected) && in_array($obj->rowid, $selected))
						    {
							    $out .= '<option value="'.$obj->rowid.'"';
							    if ($disabled) $out .= ' disabled';
							    $out .= ' selected>';
							    $out .= $contactstatic->getFullName($langs);
							    if ($showfunction && $obj->poste) $out .= ' ('.$obj->poste.')';
							    if (($showsoc > 0) && $obj->company) $out .= ' - ('.$obj->company.')';
							    $out .= '</option>';
						    }
						    else
						    {
							    $out .= '<option value="'.$obj->rowid.'"';
							    if ($disabled) $out .= ' disabled';
							    $out .= '>';
							    $out .= $contactstatic->getFullName($langs);
							    if ($showfunction && $obj->poste) $out .= ' ('.$obj->poste.')';
							    if (($showsoc > 0) && $obj->company) $out .= ' - ('.$obj->company.')';
							    $out .= '</option>';
						    }
					    }
					    else
					    {
						    if (in_array($obj->rowid, $selected))
						    {
							    $out .= $contactstatic->getFullName($langs);
							    if ($showfunction && $obj->poste) $out .= ' ('.$obj->poste.')';
							    if (($showsoc > 0) && $obj->company) $out .= ' - ('.$obj->company.')';
						    }
					    }
				    }
					$i++;
				}
			}
			else
			{
				$out .= '<option value="-1"'.(($showempty == 2 || $multiple) ? '' : ' selected').' disabled>';
				$out .= ($socid != -1) ? ($langs->trans($socid ? "NoContactDefinedForThirdParty" : "NoContactDefined")) : $langs->trans('SelectAThirdPartyFirst');
				$out .= '</option>';
			}

			$parameters = array(
				'socid'=>$socid,
				'htmlname'=>$htmlname,
				'resql'=>$resql,
				'out'=>&$out,
				'showfunction'=>$showfunction,
				'showsoc'=>$showsoc,
			);

			$reshook = $hookmanager->executeHooks('afterSelectContactOptions', $parameters, $this, $action);   

			if ($htmlname != 'none' && !$options_only)
			{
				$out .= '</select>';
			}

			$this->num = $num;
			return $out;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

      
	
    public function select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0')
	{
          
		print $this->select_dolusers($selected, $htmlname, $show_empty, $exclude, $disabled, $include, $enableonly, $force_entity);
	}

      
	
    public function select_dolusers($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0', $maxlength = 0, $showstatus = 0, $morefilter = '', $show_every = 0, $enableonlytext = '', $morecss = '', $noactive = 0, $outputmode = 0, $multiple = false)
	{
          
		global $conf, $user, $langs;

		  
		if ((is_numeric($selected) && ($selected < -2 || empty($selected))) && empty($conf->global->SOCIETE_DISABLE_DEFAULT_SALESREPRESENTATIVE)) $selected = $user->id;

		if ($selected === '') $selected = array();
		elseif (!is_array($selected)) $selected = array($selected);

		$excludeUsers = null;
		$includeUsers = null;

		  
		if (is_array($exclude))	$excludeUsers = implode(",", $exclude);
		  
		if (is_array($include))	$includeUsers = implode(",", $include);
		elseif ($include == 'hierarchy')
		{
			  
			$includeUsers = implode(",", $user->getAllChildIds(0));
		}
		elseif ($include == 'hierarchyme')
		{
			  
			$includeUsers = implode(",", $user->getAllChildIds(1));
		}

		$out = '';
		$outarray = array();

		  
		$sql = "SELECT DISTINCT u.rowid, u.lastname as lastname, u.firstname, u.statut, u.login, u.admin, u.entity";
		if (!empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && !$user->entity)
		{
			$sql .= ", e.label";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX."user as u";
		if (!empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && !$user->entity)
		{
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."entity as e ON e.rowid=u.entity";
			if ($force_entity) $sql .= " WHERE u.entity IN (0,".$force_entity.")";
			else $sql .= " WHERE u.entity IS NOT NULL";
		}
		else
		{
			if (!empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
			{
				$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ug";
				$sql .= " ON ug.fk_user = u.rowid";
				$sql .= " WHERE ug.entity = ".$conf->entity;
			}
			else
			{
				$sql .= " WHERE u.entity IN (0,".$conf->entity.")";
			}
		}
		if (!empty($user->socid)) $sql .= " AND u.fk_soc = ".$user->socid;
		if (is_array($exclude) && $excludeUsers) $sql .= " AND u.rowid NOT IN (".$excludeUsers.")";
		if ($includeUsers) $sql .= " AND u.rowid IN (".$includeUsers.")";
		if (!empty($conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX) || $noactive) $sql .= " AND u.statut <> 0";
		if (!empty($morefilter)) $sql .= " ".$morefilter;

		if (empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION))	  
		{
			$sql .= " ORDER BY u.firstname ASC";
		}
		else
		{
			$sql .= " ORDER BY u.lastname ASC";
		}

		dol_syslog(get_class($this)."::select_dolusers", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
		   		  
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname);

				  
				$out .= '<select class="flat'.($morecss ? ' minwidth100imp '.$morecss : ' minwidth200').'" id="'.$htmlname.'" name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').' '.($disabled ? ' disabled' : '').'>';
				if ($show_empty && !$multiple) $out .= '<option value="-1"'.((empty($selected) || in_array(-1, $selected)) ? ' selected' : '').'>&nbsp;</option>'."\n";
				if ($show_every) $out .= '<option value="-2"'.((in_array(-2, $selected)) ? ' selected' : '').'>-- '.$langs->trans("Everybody").' --</option>'."\n";

				$userstatic = new User($this->db);

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);

					$userstatic->id = $obj->rowid;
					$userstatic->lastname = $obj->lastname;
					$userstatic->firstname = $obj->firstname;

					$disableline = '';
					if (is_array($enableonly) && count($enableonly) && !in_array($obj->rowid, $enableonly)) $disableline = ($enableonlytext ? $enableonlytext : '1');

					if ((is_object($selected) && $selected->id == $obj->rowid) || (!is_object($selected) && in_array($obj->rowid, $selected)))
					{
						$out .= '<option value="'.$obj->rowid.'"';
						if ($disableline) $out .= ' disabled';
						$out .= ' selected>';
					}
					else
					{
						$out .= '<option value="'.$obj->rowid.'"';
						if ($disableline) $out .= ' disabled';
						$out .= '>';
					}

					  
					$fullNameMode = 0;
					if (empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION))
					{
						$fullNameMode = 1;   
					}
					$out .= $userstatic->getFullName($langs, $fullNameMode, -1, $maxlength);

					  
					$moreinfo = 0;
					if (!empty($conf->global->MAIN_SHOW_LOGIN))
					{
						$out .= ($moreinfo ? ' - ' : ' (').$obj->login;
						$moreinfo++;
					}
					if ($showstatus >= 0)
					{
						if ($obj->statut == 1 && $showstatus == 1)
						{
							$out .= ($moreinfo ? ' - ' : ' (').$langs->trans('Enabled');
							$moreinfo++;
						}
						if ($obj->statut == 0)
						{
							$out .= ($moreinfo ? ' - ' : ' (').$langs->trans('Disabled');
							$moreinfo++;
						}
					}
					if (!empty($conf->multicompany->enabled) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && $user->admin && !$user->entity)
					{
						if (!$obj->entity)
						{
							$out .= ($moreinfo ? ' - ' : ' (').$langs->trans("AllEntities");
							$moreinfo++;
						}
						else
						{
							$out .= ($moreinfo ? ' - ' : ' (').($obj->label ? $obj->label : $langs->trans("EntityNameNotDefined"));
							$moreinfo++;
					 	}
					}
					$out .= ($moreinfo ? ')' : '');
					if ($disableline && $disableline != '1')
					{
						$out .= ' - '.$disableline;   
					}
					$out .= '</option>';
					$outarray[$userstatic->id] = $userstatic->getFullName($langs, $fullNameMode, -1, $maxlength);

					$i++;
				}
			}
			else
			{
				$out .= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" disabled>';
				$out .= '<option value="">'.$langs->trans("None").'</option>';
			}
			$out .= '</select>';
		}
		else
		{
			dol_print_error($this->db);
		}

		if ($outputmode) return $outarray;
		return $out;
	}


      
	
    public function select_dolusers_forevent($action = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0', $maxlength = 0, $showstatus = 0, $morefilter = '', $showproperties = 0, $listofuserid = array(), $listofcontactid = array(), $listofotherid = array())
	{
          
		global $conf, $user, $langs;

		$userstatic = new User($this->db);
		$out = '';

		  
		  
		if ($action == 'view')
		{
			$out .= '';
		}
		else
		{
			$out .= '<input type="hidden" class="removedassignedhidden" name="removedassigned" value="">';
			$out .= '<script type="text/javascript" language="javascript">jQuery(document).ready(function () {    jQuery(".removedassigned").click(function() {        jQuery(".removedassignedhidden").val(jQuery(this).val());    });})</script>';
			$out .= $this->select_dolusers('', $htmlname, $show_empty, $exclude, $disabled, $include, $enableonly, $force_entity, $maxlength, $showstatus, $morefilter);
			$out .= ' <input type="submit" class="button valignmiddle" name="'.$action.'assignedtouser" value="'.dol_escape_htmltag($langs->trans("Add")).'">';
			$out .= '<br>';
		}
		$assignedtouser = array();
		if (!empty($_SESSION['assignedtouser']))
		{
			$assignedtouser = json_decode($_SESSION['assignedtouser'], true);
		}
		$nbassignetouser = count($assignedtouser);

		if ($nbassignetouser && $action != 'view') $out .= '<br>';
		if ($nbassignetouser) $out .= '<ul class="attendees">';
		$i = 0; $ownerid = 0;
		foreach ($assignedtouser as $key => $value)
		{
			if ($value['id'] == $ownerid) continue;

			$out .= '<li>';
			$userstatic->fetch($value['id']);
			$out .= $userstatic->getNomUrl(-1);
			if ($i == 0) { $ownerid = $value['id']; $out .= ' ('.$langs->trans("Owner").')'; }
			if ($nbassignetouser > 1 && $action != 'view')
			{
				$out .= ' <input type="image" style="border: 0px;" src="'.img_picto($langs->trans("Remove"), 'delete', '', 0, 1).'" value="'.$userstatic->id.'" class="removedassigned" id="removedassigned_'.$userstatic->id.'" name="removedassigned_'.$userstatic->id.'">';
			}
			  
			if ($showproperties)
			{
				if ($ownerid == $value['id'] && is_array($listofuserid) && count($listofuserid) && in_array($ownerid, array_keys($listofuserid)))
				{
					$out .= '<div class="myavailability inline-block">';
					$out .= '&nbsp;-&nbsp;<span class="opacitymedium">'.$langs->trans("Availability").':</span>  <input id="transparency" class="marginleftonly marginrightonly" '.($action == 'view' ? 'disabled' : '').' type="checkbox" name="transparency"'.($listofuserid[$ownerid]['transparency'] ? ' checked' : '').'>'.$langs->trans("Busy");
					$out .= '</div>';
				}
			}
			  
			  

			$out .= '</li>';
			$i++;
		}
		if ($nbassignetouser) $out .= '</ul>';

		  
		return $out;
	}


      
	
    public function select_produits($selected = '', $htmlname = 'productid', $filtertype = '', $limit = 20, $price_level = 0, $status = 1, $finished = 2, $selected_input_value = '', $hidelabel = 0, $ajaxoptions = array(), $socid = 0, $showempty = '1', $forcecombo = 0, $morecss = '', $hidepriceinlabel = 0, $warehouseStatus = '', $selected_combinations = array())
	{
          
		global $langs, $conf;

		  
		$price_level = (!empty($price_level) ? $price_level : 0);
		if (is_null($ajaxoptions)) $ajaxoptions = array();

		if (strval($filtertype) === '' && (!empty($conf->product->enabled) || !empty($conf->service->enabled))) {
			if (!empty($conf->product->enabled) && empty($conf->service->enabled)) {
				$filtertype = '0';
			}
			elseif (empty($conf->product->enabled) && !empty($conf->service->enabled)) {
				$filtertype = '1';
			}
		}

		if (!empty($conf->use_javascript_ajax) && !empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
		{
			$placeholder = '';

			if ($selected && empty($selected_input_value))
			{
				require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
				$producttmpselect = new Product($this->db);
				$producttmpselect->fetch($selected);
				$selected_input_value = $producttmpselect->ref;
				unset($producttmpselect);
			}
			  
			if ($filtertype == '')
			{
				if (empty($conf->product->enabled)) {   
					$filtertype = 1;
				}
				elseif (empty($conf->service->enabled)) {   
					$filtertype = 0;
				}
			}
			  
			$urloption = 'htmlname='.$htmlname.'&outjson=1&price_level='.$price_level.'&type='.$filtertype.'&mode=1&status='.$status.'&finished='.$finished.'&hidepriceinlabel='.$hidepriceinlabel.'&warehousestatus='.$warehouseStatus;
			  
			if (!empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid)) {
				$urloption .= '&socid='.$socid;
			}
			print ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/product/ajax/products.php', $urloption, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);

			if (!empty($conf->variants->enabled)) {
				?>
				<script>

					selected = <?php echo json_encode($selected_combinations) ?>;
					combvalues = {};

					jQuery(document).ready(function () {

						jQuery("input[name='prod_entry_mode']").change(function () {
							if (jQuery(this).val() == 'free') {
								jQuery('div#attributes_box').empty();
							}
						});

						jQuery("input#<?php echo $htmlname ?>").change(function () {

							if (!jQuery(this).val()) {
								jQuery('div#attributes_box').empty();
								return;
							}

							jQuery.getJSON("<?php echo dol_buildpath('/variants/ajax/getCombinations.php', 2) ?>", {
								id: jQuery(this).val()
							}, function (data) {
								jQuery('div#attributes_box').empty();

								jQuery.each(data, function (key, val) {

									combvalues[val.id] = val.values;

									var span = jQuery(document.createElement('div')).css({
										'display': 'table-row'
									});

									span.append(
										jQuery(document.createElement('div')).text(val.label).css({
											'font-weight': 'bold',
											'display': 'table-cell',
											'text-align': 'right'
										})
									);

									var html = jQuery(document.createElement('select')).attr('name', 'combinations[' + val.id + ']').css({
										'margin-left': '15px',
										'white-space': 'pre'
									}).append(
										jQuery(document.createElement('option')).val('')
									);

									jQuery.each(combvalues[val.id], function (key, val) {
										var tag = jQuery(document.createElement('option')).val(val.id).html(val.value);

										if (selected[val.fk_product_attribute] == val.id) {
											tag.attr('selected', 'selected');
										}

										html.append(tag);
									});

									span.append(html);
									jQuery('div#attributes_box').append(span);
								});
							})
						});

						<?php if ($selected): ?>
						jQuery("input#<?php echo $htmlname ?>").change();
						<?php endif ?>
					});
				</script>
                <?php
			}
			if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
			elseif ($hidelabel > 1) {
				$placeholder = ' placeholder="'.$langs->trans("RefOrLabel").'"';
				if ($hidelabel == 2) {
					print img_picto($langs->trans("Search"), 'search');
				}
			}
			print '<input type="text" class="minwidth100" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->PRODUCT_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
			if ($hidelabel == 3) {
				print img_picto($langs->trans("Search"), 'search');
			}
		}
		else
		{
			print $this->select_produits_list($selected, $htmlname, $filtertype, $limit, $price_level, '', $status, $finished, 0, $socid, $showempty, $forcecombo, $morecss, $hidepriceinlabel, $warehouseStatus);
		}
	}

      
	
    public function select_produits_list($selected = '', $htmlname = 'productid', $filtertype = '', $limit = 20, $price_level = 0, $filterkey = '', $status = 1, $finished = 2, $outputmode = 0, $socid = 0, $showempty = '1', $forcecombo = 0, $morecss = '', $hidepriceinlabel = 0, $warehouseStatus = '')
	{
          
		global $langs, $conf, $user, $db;

		$out = '';
		$outarray = array();

          
        if ($conf->global->PRODUCT_USE_UNITS) {
            $langs->load('other');
        }

		$warehouseStatusArray = array();
		if (!empty($warehouseStatus))
		{
			require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
			if (preg_match('/warehouseclosed/', $warehouseStatus))
			{
				$warehouseStatusArray[] = Entrepot::STATUS_CLOSED;
			}
			if (preg_match('/warehouseopen/', $warehouseStatus))
			{
				$warehouseStatusArray[] = Entrepot::STATUS_OPEN_ALL;
			}
			if (preg_match('/warehouseinternal/', $warehouseStatus))
			{
				$warehouseStatusArray[] = Entrepot::STATUS_OPEN_INTERNAL;
			}
		}

		$selectFields = " p.rowid, p.ref, p.label, p.description, p.barcode, p.fk_product_type, p.price, p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression";
		if (count($warehouseStatusArray))
		{
		    $selectFieldsGrouped = ", sum(".$db->ifsql("e.statut IS NULL", "0", "ps.reel").") as stock";   
		}
		else
		{
		    $selectFieldsGrouped = ", ".$db->ifsql("p.stock IS NULL", 0, "p.stock")." AS stock";
		}

		$sql = "SELECT ";
		$sql .= $selectFields.$selectFieldsGrouped;

		if (!empty($conf->global->PRODUCT_SORT_BY_CATEGORY))
		{
			  
			$sql .= ", (SELECT ".MAIN_DB_PREFIX."categorie_product.fk_categorie
						FROM ".MAIN_DB_PREFIX."categorie_product
						WHERE ".MAIN_DB_PREFIX."categorie_product.fk_product=p.rowid
						LIMIT 1
				) AS categorie_product_id ";
		}

		  
		if (!empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid))
		{
			$sql .= ', pcp.rowid as idprodcustprice, pcp.price as custprice, pcp.price_ttc as custprice_ttc,';
			$sql .= ' pcp.price_base_type as custprice_base_type, pcp.tva_tx as custtva_tx';
			$selectFields .= ", idprodcustprice, custprice, custprice_ttc, custprice_base_type, custtva_tx";
		}
          
        if (!empty($conf->global->PRODUCT_USE_UNITS)) {
            $sql .= ", u.label as unit_long, u.short_label as unit_short, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units";
            $selectFields .= ', unit_long, unit_short, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units';
        }

		  
		if (!empty($conf->global->MAIN_MULTILANGS))
		{
			$sql .= ", pl.label as label_translated";
			$selectFields .= ", label_translated";
		}
		  
		if (!empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) || !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES))
		{
			$sql .= ", (SELECT pp.rowid FROM ".MAIN_DB_PREFIX."product_price as pp WHERE pp.fk_product = p.rowid";
			if ($price_level >= 1 && !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)) $sql .= " AND price_level=".$price_level;
			$sql .= " ORDER BY date_price";
			$sql .= " DESC LIMIT 1) as price_rowid";
			$sql .= ", (SELECT pp.price_by_qty FROM ".MAIN_DB_PREFIX."product_price as pp WHERE pp.fk_product = p.rowid";   
			if ($price_level >= 1 && !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)) $sql .= " AND price_level=".$price_level;
			$sql .= " ORDER BY date_price";
			$sql .= " DESC LIMIT 1) as price_by_qty";
			$selectFields .= ", price_rowid, price_by_qty";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
		if (count($warehouseStatusArray))
		{
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock as ps on ps.fk_product = p.rowid";
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."entrepot as e on ps.fk_entrepot = e.rowid AND e.entity IN (".getEntity('stock').")";
			$sql .= ' AND e.statut IN ('.$this->db->escape(implode(',', $warehouseStatusArray)).')';   
		}

		  
		if (!empty($conf->global->MAIN_SEARCH_PRODUCT_BY_FOURN_REF))
		{
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
		}

		  
		if (!empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid)) {
			$sql .= " LEFT JOIN  ".MAIN_DB_PREFIX."product_customer_price as pcp ON pcp.fk_soc=".$socid." AND pcp.fk_product=p.rowid";
		}
          
        if (!empty($conf->global->PRODUCT_USE_UNITS)) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_units u ON u.rowid = p.fk_unit";
        }
		  
		if (!empty($conf->global->MAIN_MULTILANGS))
		{
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_lang as pl ON pl.fk_product = p.rowid AND pl.lang='".$langs->getDefaultLang()."'";
		}

		if (!empty($conf->global->PRODUIT_ATTRIBUTES_HIDECHILD)) {
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac ON pac.fk_product_child = p.rowid";
		}

		$sql .= ' WHERE p.entity IN ('.getEntity('product').')';

		if (!empty($conf->global->PRODUIT_ATTRIBUTES_HIDECHILD)) {
			$sql .= " AND pac.rowid IS NULL";
		}

		if ($finished == 0)
		{
			$sql .= " AND p.finished = ".$finished;
		}
		elseif ($finished == 1)
		{
			$sql .= " AND p.finished = ".$finished;
			if ($status >= 0)  $sql .= " AND p.tosell = ".$status;
		}
		elseif ($status >= 0)
		{
			$sql .= " AND p.tosell = ".$status;
		}
		  
		if (strval($filtertype) != '') $sql .= " AND p.fk_product_type = ".$filtertype;
		elseif (empty($conf->product->enabled)) {   
			$sql .= " AND p.fk_product_type = 1";
		}
		elseif (empty($conf->service->enabled)) {   
			$sql .= " AND p.fk_product_type = 0";
		}
		  
		if ($filterkey != '')
		{
			$sql .= ' AND (';
			$prefix = empty($conf->global->PRODUCT_DONOTSEARCH_ANYWHERE) ? '%' : '';   
			  
			$scrit = explode(' ', $filterkey);
			$i = 0;
			if (count($scrit) > 1) $sql .= "(";
			foreach ($scrit as $crit)
			{
				if ($i > 0) $sql .= " AND ";
				$sql .= "(p.ref LIKE '".$db->escape($prefix.$crit)."%' OR p.label LIKE '".$db->escape($prefix.$crit)."%'";
				if (!empty($conf->global->MAIN_MULTILANGS)) $sql .= " OR pl.label LIKE '".$db->escape($prefix.$crit)."%'";
				if (!empty($conf->global->PRODUCT_AJAX_SEARCH_ON_DESCRIPTION))
				{
					$sql .= " OR p.description LIKE '".$db->escape($prefix.$crit)."%'";
					if (!empty($conf->global->MAIN_MULTILANGS)) $sql .= " OR pl.description LIKE '".$db->escape($prefix.$crit)."%'";
				}
				if (!empty($conf->global->MAIN_SEARCH_PRODUCT_BY_FOURN_REF)) $sql .= " OR pfp.ref_fourn LIKE '".$db->escape($prefix.$crit)."%'";
				$sql .= ")";
				$i++;
			}
			if (count($scrit) > 1) $sql .= ")";
		  	if (!empty($conf->barcode->enabled)) $sql .= " OR p.barcode LIKE '".$db->escape($prefix.$filterkey)."%'";
			$sql .= ')';
		}
		if (count($warehouseStatusArray))
		{
			$sql .= ' GROUP BY'.$selectFields;
		}

		  
		if (!empty($conf->global->PRODUCT_SORT_BY_CATEGORY))
		{
			$sql .= " ORDER BY categorie_product_id ";
			  
			($conf->global->PRODUCT_SORT_BY_CATEGORY == 1) ? $sql .= "ASC" : $sql .= "DESC";
		}
		else
		{
			$sql .= $db->order("p.ref");
		}

		$sql .= $db->plimit($limit, 0);

		  
		dol_syslog(get_class($this)."::select_produits_list search product", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
			require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';
			$num = $this->db->num_rows($result);

			$events = null;

			if (!$forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, $events, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT);
			}

			$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'" id="'.$htmlname.'">';

			$textifempty = '';
			  
			  
			if (!empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
			{
				if ($showempty && !is_numeric($showempty)) $textifempty = $langs->trans($showempty);
				else $textifempty .= $langs->trans("All");
			}
			else
			{
			    if ($showempty && !is_numeric($showempty)) $textifempty = $langs->trans($showempty);
			}
			if ($showempty) $out .= '<option value="0" selected>'.$textifempty.'</option>';

			$i = 0;
			while ($num && $i < $num)
			{
				$opt = '';
				$optJson = array();
				$objp = $this->db->fetch_object($result);

				if ((!empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) || !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)) && !empty($objp->price_by_qty) && $objp->price_by_qty == 1)
				{   
					$sql = "SELECT rowid, quantity, price, unitprice, remise_percent, remise, price_base_type";
					$sql .= " FROM ".MAIN_DB_PREFIX."product_price_by_qty";
					$sql .= " WHERE fk_product_price=".$objp->price_rowid;
					$sql .= " ORDER BY quantity ASC";

					dol_syslog(get_class($this)."::select_produits_list search price by qty", LOG_DEBUG);
					$result2 = $this->db->query($sql);
					if ($result2)
					{
						$nb_prices = $this->db->num_rows($result2);
						$j = 0;
						while ($nb_prices && $j < $nb_prices) {
							$objp2 = $this->db->fetch_object($result2);

							$objp->price_by_qty_rowid = $objp2->rowid;
							$objp->price_by_qty_price_base_type = $objp2->price_base_type;
							$objp->price_by_qty_quantity = $objp2->quantity;
							$objp->price_by_qty_unitprice = $objp2->unitprice;
							$objp->price_by_qty_remise_percent = $objp2->remise_percent;
							  
							$objp->quantity = $objp2->quantity;
							$objp->price = $objp2->price;
							$objp->unitprice = $objp2->unitprice;
							$objp->remise_percent = $objp2->remise_percent;
							$objp->remise = $objp2->remise;

							$this->constructProductListOption($objp, $opt, $optJson, 0, $selected, $hidepriceinlabel, $filterkey);

							$j++;

							  
							  
							  
							$out .= $opt;
							array_push($outarray, $optJson);
						}
					}
				}
				else
				{
					if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_price_expression)) {
						$price_product = new Product($this->db);
						$price_product->fetch($objp->rowid, '', '', 1);
						$priceparser = new PriceParser($this->db);
						$price_result = $priceparser->parseProduct($price_product);
						if ($price_result >= 0) {
							$objp->price = $price_result;
							$objp->unitprice = $price_result;
							  
							$objp->price_ttc = price2num($objp->price) * (1 + ($objp->tva_tx / 100));
							$objp->price_ttc = price2num($objp->price_ttc, 'MU');
						}
					}

					$this->constructProductListOption($objp, $opt, $optJson, $price_level, $selected, $hidepriceinlabel, $filterkey);
					  
					  
					  
					$out .= $opt;
					array_push($outarray, $optJson);
				}

				$i++;
			}

			$out .= '</select>';

			$this->db->free($result);

			if (empty($outputmode)) return $out;
			return $outarray;
		}
		else
		{
			dol_print_error($db);
		}
	}

	
	protected function constructProductListOption(&$objp, &$opt, &$optJson, $price_level, $selected, $hidepriceinlabel = 0, $filterkey = '')
	{
		global $langs, $conf, $user, $db;

		$outkey = '';
		$outval = '';
		$outref = '';
		$outlabel = '';
		$outdesc = '';
		$outbarcode = '';
		$outtype = '';
		$outprice_ht = '';
		$outprice_ttc = '';
		$outpricebasetype = '';
		$outtva_tx = '';
		$outqty = 1;
		$outdiscount = 0;

		$maxlengtharticle = (empty($conf->global->PRODUCT_MAX_LENGTH_COMBO) ? 48 : $conf->global->PRODUCT_MAX_LENGTH_COMBO);

		$label = $objp->label;
		if (!empty($objp->label_translated)) $label = $objp->label_translated;
		if (!empty($filterkey) && $filterkey != '') $label = preg_replace('/('.preg_quote($filterkey).')/i', '<strong>$1</strong>', $label, 1);

		$outkey = $objp->rowid;
		$outref = $objp->ref;
		$outlabel = $objp->label;
		$outdesc = $objp->description;
		$outbarcode = $objp->barcode;
		$outpbq = empty($objp->price_by_qty_rowid) ? '' : $objp->price_by_qty_rowid;

		$outtype = $objp->fk_product_type;
		$outdurationvalue = $outtype == Product::TYPE_SERVICE ?substr($objp->duration, 0, dol_strlen($objp->duration) - 1) : '';
		$outdurationunit = $outtype == Product::TYPE_SERVICE ?substr($objp->duration, -1) : '';

          
        $outvalUnits = '';
        if (!empty($conf->global->PRODUCT_USE_UNITS)) {
            if (!empty($objp->unit_short)) {
                $outvalUnits .= ' - '.$objp->unit_short;
            }
        }
        if (!empty($conf->global->PRODUCT_SHOW_DIMENSIONS_IN_COMBO)) {
            if (!empty($objp->weight) && $objp->weight_units !== null) {
                $unitToShow = showDimensionInBestUnit($objp->weight, $objp->weight_units, 'weight', $langs);
                $outvalUnits .= ' - '.$unitToShow;
            }
            if ((!empty($objp->length) || !empty($objp->width) || !empty($objp->height)) && $objp->length_units !== null) {
            	$unitToShow = $objp->length.' x '.$objp->width.' x '.$objp->height.' '.measuringUnitString(0, 'size', $objp->length_units);
                $outvalUnits .= ' - '.$unitToShow;
            }
            if (!empty($objp->surface) && $objp->surface_units !== null) {
                $unitToShow = showDimensionInBestUnit($objp->surface, $objp->surface_units, 'surface', $langs);
                $outvalUnits .= ' - '.$unitToShow;
            }
            if (!empty($objp->volume) && $objp->volume_units !== null) {
                $unitToShow = showDimensionInBestUnit($objp->volume, $objp->volume_units, 'volume', $langs);
                $outvalUnits .= ' - '.$unitToShow;
            }
        }
        if ($outdurationvalue && $outdurationunit) {
            $da = array(
                'h' => $langs->trans('Hour'),
                'd' => $langs->trans('Day'),
                'w' => $langs->trans('Week'),
                'm' => $langs->trans('Month'),
                'y' => $langs->trans('Year')
            );
            if (isset($da[$outdurationunit])) {
                $outvalUnits .= ' - '.$outdurationvalue.' '.$langs->transnoentities($da[$outdurationunit].($outdurationvalue > 1 ? 's' : ''));
            }
        }

		$opt = '<option value="'.$objp->rowid.'"';
		$opt .= ($objp->rowid == $selected) ? ' selected' : '';
		if (!empty($objp->price_by_qty_rowid) && $objp->price_by_qty_rowid > 0)
		{
			$opt .= ' pbq="'.$objp->price_by_qty_rowid.'" data-pbq="'.$objp->price_by_qty_rowid.'" data-pbqup="'.$objp->price_by_qty_unitprice.'" data-pbqbase="'.$objp->price_by_qty_price_base_type.'" data-pbqqty="'.$objp->price_by_qty_quantity.'" data-pbqpercent="'.$objp->price_by_qty_remise_percent.'"';
		}
		if (!empty($conf->stock->enabled) && isset($objp->stock) && ($objp->fk_product_type == Product::TYPE_PRODUCT || !empty($conf->global->STOCK_SUPPORTS_SERVICES)))
		{
		    if (!empty($user->rights->stock->lire)) {
    			if ($objp->stock > 0) $opt .= ' class="product_line_stock_ok"';
	       		elseif ($objp->stock <= 0) $opt .= ' class="product_line_stock_too_low"';
		    }
		}
		$opt .= '>';
		$opt .= $objp->ref;
		if ($outbarcode) $opt .= ' ('.$outbarcode.')';
		$opt .= ' - '.dol_trunc($label, $maxlengtharticle);

		$objRef = $objp->ref;
		if (!empty($filterkey) && $filterkey != '') $objRef = preg_replace('/('.preg_quote($filterkey).')/i', '<strong>$1</strong>', $objRef, 1);
		$outval .= $objRef;
		if ($outbarcode) $outval .= ' ('.$outbarcode.')';
		$outval .= ' - '.dol_trunc($label, $maxlengtharticle);
          
        $opt .= $outvalUnits;
        $outval .= $outvalUnits;

		$found = 0;

		  
		  
		if (empty($hidepriceinlabel) && $price_level >= 1 && (!empty($conf->global->PRODUIT_MULTIPRICES) || !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)))
		{
			$sql = "SELECT price, price_ttc, price_base_type, tva_tx";
			$sql .= " FROM ".MAIN_DB_PREFIX."product_price";
			$sql .= " WHERE fk_product='".$objp->rowid."'";
			$sql .= " AND entity IN (".getEntity('productprice').")";
			$sql .= " AND price_level=".$price_level;
			$sql .= " ORDER BY date_price DESC, rowid DESC";   
			$sql .= " LIMIT 1";

			dol_syslog(get_class($this).'::constructProductListOption search price for product '.$objp->rowid.' AND level '.$price_level.'', LOG_DEBUG);
			$result2 = $this->db->query($sql);
			if ($result2)
			{
				$objp2 = $this->db->fetch_object($result2);
				if ($objp2)
				{
					$found = 1;
					if ($objp2->price_base_type == 'HT')
					{
						$opt .= ' - '.price($objp2->price, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("HT");
						$outval .= ' - '.price($objp2->price, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("HT");
					}
					else
					{
						$opt .= ' - '.price($objp2->price_ttc, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("TTC");
						$outval .= ' - '.price($objp2->price_ttc, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("TTC");
					}
					$outprice_ht = price($objp2->price);
					$outprice_ttc = price($objp2->price_ttc);
					$outpricebasetype = $objp2->price_base_type;
					$outtva_tx = $objp2->tva_tx;
				}
			}
			else
			{
				dol_print_error($this->db);
			}
		}

		  
		if (empty($hidepriceinlabel) && !empty($objp->quantity) && $objp->quantity >= 1 && (!empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) || !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)))
		{
			$found = 1;
			$outqty = $objp->quantity;
			$outdiscount = $objp->remise_percent;
			if ($objp->quantity == 1)
			{
				$opt .= ' - '.price($objp->unitprice, 1, $langs, 0, 0, -1, $conf->currency)."/";
				$outval .= ' - '.price($objp->unitprice, 0, $langs, 0, 0, -1, $conf->currency)."/";
				$opt .= $langs->trans("Unit");   
				$outval .= $langs->transnoentities("Unit");
			}
			else
			{
				$opt .= ' - '.price($objp->price, 1, $langs, 0, 0, -1, $conf->currency)."/".$objp->quantity;
				$outval .= ' - '.price($objp->price, 0, $langs, 0, 0, -1, $conf->currency)."/".$objp->quantity;
				$opt .= $langs->trans("Units");   
				$outval .= $langs->transnoentities("Units");
			}

			$outprice_ht = price($objp->unitprice);
			$outprice_ttc = price($objp->unitprice * (1 + ($objp->tva_tx / 100)));
			$outpricebasetype = $objp->price_base_type;
			$outtva_tx = $objp->tva_tx;
		}
		if (empty($hidepriceinlabel) && !empty($objp->quantity) && $objp->quantity >= 1)
		{
			$opt .= " (".price($objp->unitprice, 1, $langs, 0, 0, -1, $conf->currency)."/".$langs->trans("Unit").")";   
			$outval .= " (".price($objp->unitprice, 0, $langs, 0, 0, -1, $conf->currency)."/".$langs->transnoentities("Unit").")";   
		}
		if (empty($hidepriceinlabel) && !empty($objp->remise_percent) && $objp->remise_percent >= 1)
		{
			$opt .= " - ".$langs->trans("Discount")." : ".vatrate($objp->remise_percent).' %';
			$outval .= " - ".$langs->transnoentities("Discount")." : ".vatrate($objp->remise_percent).' %';
		}

		  
		if (empty($hidepriceinlabel) && !empty($conf->global->PRODUIT_CUSTOMER_PRICES))
		{
			if (!empty($objp->idprodcustprice))
			{
				$found = 1;

				if ($objp->custprice_base_type == 'HT')
				{
					$opt .= ' - '.price($objp->custprice, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("HT");
					$outval .= ' - '.price($objp->custprice, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("HT");
				}
				else
				{
					$opt .= ' - '.price($objp->custprice_ttc, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("TTC");
					$outval .= ' - '.price($objp->custprice_ttc, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("TTC");
				}

				$outprice_ht = price($objp->custprice);
				$outprice_ttc = price($objp->custprice_ttc);
				$outpricebasetype = $objp->custprice_base_type;
				$outtva_tx = $objp->custtva_tx;
			}
		}

		  
		if (empty($hidepriceinlabel) && !$found)
		{
			if ($objp->price_base_type == 'HT')
			{
				$opt .= ' - '.price($objp->price, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("HT");
				$outval .= ' - '.price($objp->price, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("HT");
			}
			else
			{
				$opt .= ' - '.price($objp->price_ttc, 1, $langs, 0, 0, -1, $conf->currency).' '.$langs->trans("TTC");
				$outval .= ' - '.price($objp->price_ttc, 0, $langs, 0, 0, -1, $conf->currency).' '.$langs->transnoentities("TTC");
			}
			$outprice_ht = price($objp->price);
			$outprice_ttc = price($objp->price_ttc);
			$outpricebasetype = $objp->price_base_type;
			$outtva_tx = $objp->tva_tx;
		}

		if (!empty($conf->stock->enabled) && isset($objp->stock) && ($objp->fk_product_type == Product::TYPE_PRODUCT || !empty($conf->global->STOCK_SUPPORTS_SERVICES)))
		{
	        if (!empty($user->rights->stock->lire)) {
                $opt .= ' - '.$langs->trans("Stock").':'.$objp->stock;

    			if ($objp->stock > 0) {
    				$outval .= ' - <span class="product_line_stock_ok">';
    			}elseif ($objp->stock <= 0) {
    				$outval .= ' - <span class="product_line_stock_too_low">';
    			}
    			$outval .= $langs->transnoentities("Stock").':'.$objp->stock;
    			$outval .= '</span>';
    			if (!empty($conf->global->STOCK_SHOW_VIRTUAL_STOCK_IN_PRODUCTS_COMBO))    
    			{
    			    $langs->load("stocks");

    			    $tmpproduct = new Product($this->db);
    			    $tmpproduct->fetch($objp->rowid, '', '', '', 1, 1, 1);	  
    			    $tmpproduct->load_virtual_stock();
    			    $virtualstock = $tmpproduct->stock_theorique;

    			    $opt .= ' - '.$langs->trans("VirtualStock").':'.$virtualstock;

    			    $outval .= ' - '.$langs->transnoentities("VirtualStock").':';
    			    if ($virtualstock > 0) {
    			        $outval .= ' - <span class="product_line_stock_ok">';
    			    }elseif ($virtualstock <= 0) {
    			        $outval .= ' - <span class="product_line_stock_too_low">';
    			    }
    			    $outval .= $virtualstock;
    			    $outval .= '</span>';

    			    unset($tmpproduct);
    			}
	        }
		}

		$opt .= "</option>\n";
		$optJson = array('key'=>$outkey, 'value'=>$outref, 'label'=>$outval, 'label2'=>$outlabel, 'desc'=>$outdesc, 'type'=>$outtype, 'price_ht'=>price2num($outprice_ht), 'price_ttc'=>price2num($outprice_ttc), 'pricebasetype'=>$outpricebasetype, 'tva_tx'=>$outtva_tx, 'qty'=>$outqty, 'discount'=>$outdiscount, 'duration_value'=>$outdurationvalue, 'duration_unit'=>$outdurationunit, 'pbq'=>$outpbq);
	}

      
	
    public function select_produits_fournisseurs($socid, $selected = '', $htmlname = 'productid', $filtertype = '', $filtre = '', $ajaxoptions = array(), $hidelabel = 0, $alsoproductwithnosupplierprice = 0, $morecss = '')
	{
          
		global $langs, $conf;
		global $price_level, $status, $finished;

		$selected_input_value = '';
		if (!empty($conf->use_javascript_ajax) && !empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
		{
			if ($selected > 0)
			{
				require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
				$producttmpselect = new Product($this->db);
				$producttmpselect->fetch($selected);
				$selected_input_value = $producttmpselect->ref;
				unset($producttmpselect);
			}

			  
			$urloption = ($socid > 0 ? 'socid='.$socid.'&' : '').'htmlname='.$htmlname.'&outjson=1&price_level='.$price_level.'&type='.$filtertype.'&mode=2&status='.$status.'&finished='.$finished.'&alsoproductwithnosupplierprice='.$alsoproductwithnosupplierprice;
			print ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/product/ajax/products.php', $urloption, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			print ($hidelabel ? '' : $langs->trans("RefOrLabel").' : ').'<input type="text" size="20" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'">';
		}
		else
		{
			print $this->select_produits_fournisseurs_list($socid, $selected, $htmlname, $filtertype, $filtre, '', -1, 0, 0, $alsoproductwithnosupplierprice, $morecss);
		}
	}

      
	
    public function select_produits_fournisseurs_list($socid, $selected = '', $htmlname = 'productid', $filtertype = '', $filtre = '', $filterkey = '', $statut = -1, $outputmode = 0, $limit = 100, $alsoproductwithnosupplierprice = 0, $morecss = '')
	{
          
		global $langs, $conf, $db;

		$out = '';
		$outarray = array();

		$maxlengtharticle = (empty($conf->global->PRODUCT_MAX_LENGTH_COMBO) ? 48 : $conf->global->PRODUCT_MAX_LENGTH_COMBO);

		$langs->load('stocks');
          
        if ($conf->global->PRODUCT_USE_UNITS) {
            $langs->load('other');
        }

		$sql = "SELECT p.rowid, p.ref, p.label, p.price, p.duration, p.fk_product_type,";
		$sql .= " pfp.ref_fourn, pfp.rowid as idprodfournprice, pfp.price as fprice, pfp.quantity, pfp.remise_percent, pfp.remise, pfp.unitprice,";
		$sql .= " pfp.fk_supplier_price_expression, pfp.fk_product, pfp.tva_tx, pfp.fk_soc, s.nom as name,";
		$sql .= " pfp.supplier_reputation";
          
        if ($conf->global->PRODUCT_USE_UNITS) {
            $sql .= ", u.label as unit_long, u.short_label as unit_short, p.weight, p.weight_units, p.length, p.length_units, p.width, p.width_units, p.height, p.height_units, p.surface, p.surface_units, p.volume, p.volume_units";
        }
        if (!empty($conf->barcode->enabled)) $sql .= ", pfp.barcode";
		$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
		if ($socid) $sql .= " AND pfp.fk_soc = ".$socid;
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON pfp.fk_soc = s.rowid";
          
        if ($conf->global->PRODUCT_USE_UNITS) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_units u ON u.rowid = p.fk_unit";
        }
		$sql .= " WHERE p.entity IN (".getEntity('product').")";
		$sql .= " AND p.tobuy = 1";
		if (strval($filtertype) != '') $sql .= " AND p.fk_product_type=".$this->db->escape($filtertype);
		if (!empty($filtre)) $sql .= " ".$filtre;
		  
		if ($filterkey != '')
		{
			$sql .= ' AND (';
			$prefix = empty($conf->global->PRODUCT_DONOTSEARCH_ANYWHERE) ? '%' : '';   
			  
			$scrit = explode(' ', $filterkey);
			$i = 0;
			if (count($scrit) > 1) $sql .= "(";
			foreach ($scrit as $crit)
			{
				if ($i > 0) $sql .= " AND ";
				$sql .= "(pfp.ref_fourn LIKE '".$this->db->escape($prefix.$crit)."%' OR p.ref LIKE '".$this->db->escape($prefix.$crit)."%' OR p.label LIKE '".$this->db->escape($prefix.$crit)."%')";
				$i++;
			}
			if (count($scrit) > 1) $sql .= ")";
			if (!empty($conf->barcode->enabled)) {
                $sql .= " OR p.barcode LIKE '".$this->db->escape($prefix.$filterkey)."%'";
                $sql .= " OR pfp.barcode LIKE '".$this->db->escape($prefix.$filterkey)."%'";
            }
			$sql .= ')';
		}
		$sql .= " ORDER BY pfp.ref_fourn DESC, pfp.quantity ASC";
		$sql .= $db->plimit($limit, 0);

		  

		dol_syslog(get_class($this)."::select_produits_fournisseurs_list", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';

			$num = $this->db->num_rows($result);

			  
			$out .= '<select class="flat maxwidthonsmartphone'.($morecss ? ' '.$morecss : '').'" id="'.$htmlname.'" name="'.$htmlname.'">';
			if (!$selected) $out .= '<option value="0" selected>&nbsp;</option>';
			else $out .= '<option value="0">&nbsp;</option>';

			$i = 0;
			while ($i < $num)
			{
				$objp = $this->db->fetch_object($result);

				$outkey = $objp->idprodfournprice;   
				if (!$outkey && $alsoproductwithnosupplierprice) $outkey = 'idprod_'.$objp->rowid;   

				$outref = $objp->ref;
				$outval = '';
				$outbarcode = $objp->barcode;
				$outqty = 1;
				$outdiscount = 0;
				$outtype = $objp->fk_product_type;
				$outdurationvalue = $outtype == Product::TYPE_SERVICE ?substr($objp->duration, 0, dol_strlen($objp->duration) - 1) : '';
				$outdurationunit = $outtype == Product::TYPE_SERVICE ?substr($objp->duration, -1) : '';

                  
                $outvalUnits = '';
                if ($conf->global->PRODUCT_USE_UNITS) {
                    if (!empty($objp->unit_short)) {
                        $outvalUnits .= ' - '.$objp->unit_short;
                    }
                    if (!empty($objp->weight) && $objp->weight_units !== null) {
                        $unitToShow = showDimensionInBestUnit($objp->weight, $objp->weight_units, 'weight', $langs);
                        $outvalUnits .= ' - '.$unitToShow;
                    }
                    if ((!empty($objp->length) || !empty($objp->width) || !empty($objp->height)) && $objp->length_units !== null) {
                    	$unitToShow = $objp->length.' x '.$objp->width.' x '.$objp->height.' '.measuringUnitString(0, 'size', $objp->length_units);
                        $outvalUnits .= ' - '.$unitToShow;
                    }
                    if (!empty($objp->surface) && $objp->surface_units !== null) {
                        $unitToShow = showDimensionInBestUnit($objp->surface, $objp->surface_units, 'surface', $langs);
                        $outvalUnits .= ' - '.$unitToShow;
                    }
                    if (!empty($objp->volume) && $objp->volume_units !== null) {
                        $unitToShow = showDimensionInBestUnit($objp->volume, $objp->volume_units, 'volume', $langs);
                        $outvalUnits .= ' - '.$unitToShow;
                    }
                    if ($outdurationvalue && $outdurationunit) {
                        $da = array(
                            'h' => $langs->trans('Hour'),
                            'd' => $langs->trans('Day'),
                            'w' => $langs->trans('Week'),
                            'm' => $langs->trans('Month'),
                            'y' => $langs->trans('Year')
                        );
                        if (isset($da[$outdurationunit])) {
                            $outvalUnits .= ' - '.$outdurationvalue.' '.$langs->transnoentities($da[$outdurationunit].($outdurationvalue > 1 ? 's' : ''));
                        }
                    }
                }

				$objRef = $objp->ref;
				if ($filterkey && $filterkey != '') $objRef = preg_replace('/('.preg_quote($filterkey).')/i', '<strong>$1</strong>', $objRef, 1);
				$objRefFourn = $objp->ref_fourn;
				if ($filterkey && $filterkey != '') $objRefFourn = preg_replace('/('.preg_quote($filterkey).')/i', '<strong>$1</strong>', $objRefFourn, 1);
				$label = $objp->label;
				if ($filterkey && $filterkey != '') $label = preg_replace('/('.preg_quote($filterkey).')/i', '<strong>$1</strong>', $label, 1);

				$optlabel = $objp->ref;
				if (!empty($objp->idprodfournprice) && ($objp->ref != $objp->ref_fourn)) {
					$optlabel .= ' <span class=\'opacitymedium\'>('.$objp->ref_fourn.')</span>';
				}
				if (!empty($conf->barcode->enabled) && !empty($objp->barcode)) {
					$optlabel .= ' ('.$outbarcode.')';
				}
				$optlabel .= ' - '.dol_trunc($label, $maxlengtharticle);

				$outvallabel = $objRef;
				if (!empty($objp->idprodfournprice) && ($objp->ref != $objp->ref_fourn)) {
					$outvallabel .= ' ('.$objRefFourn.')';
				}
				if (!empty($conf->barcode->enabled) && !empty($objp->barcode)) {
					$outvallabel .= ' ('.$outbarcode.')';
				}
				$outvallabel .= ' - '.dol_trunc($label, $maxlengtharticle);

                  
				$optlabel .= $outvalUnits;
				$outvallabel .= $outvalUnits;

				if (!empty($objp->idprodfournprice))
				{
					$outqty = $objp->quantity;
					$outdiscount = $objp->remise_percent;
					if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_supplier_price_expression)) {
						$prod_supplier = new ProductFournisseur($this->db);
						$prod_supplier->product_fourn_price_id = $objp->idprodfournprice;
						$prod_supplier->id = $objp->fk_product;
						$prod_supplier->fourn_qty = $objp->quantity;
						$prod_supplier->fourn_tva_tx = $objp->tva_tx;
						$prod_supplier->fk_supplier_price_expression = $objp->fk_supplier_price_expression;
						$priceparser = new PriceParser($this->db);
						$price_result = $priceparser->parseProductSupplier($prod_supplier);
						if ($price_result >= 0) {
							$objp->fprice = $price_result;
							if ($objp->quantity >= 1)
							{
								$objp->unitprice = $objp->fprice / $objp->quantity;	  
							}
						}
					}
					if ($objp->quantity == 1)
					{
						$optlabel .= ' - '.price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/";
						$outvallabel .= ' - '.price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 0, $langs, 0, 0, -1, $conf->currency)."/";
						$optlabel .= $langs->trans("Unit");   
						$outvallabel .= $langs->transnoentities("Unit");
					}
					else
					{
						$optlabel .= ' - '.price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/".$objp->quantity;
						$outvallabel .= ' - '.price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 0, $langs, 0, 0, -1, $conf->currency)."/".$objp->quantity;
						$optlabel .= ' '.$langs->trans("Units");   
						$outvallabel .= ' '.$langs->transnoentities("Units");
					}

					if ($objp->quantity > 1)
					{
						$optlabel .= " (".price($objp->unitprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/".$langs->trans("Unit").")";   
						$outvallabel .= " (".price($objp->unitprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 0, $langs, 0, 0, -1, $conf->currency)."/".$langs->transnoentities("Unit").")";   
					}
					if ($objp->remise_percent >= 1)
					{
						$optlabel .= " - ".$langs->trans("Discount")." : ".vatrate($objp->remise_percent).' %';
						$outvallabel .= " - ".$langs->transnoentities("Discount")." : ".vatrate($objp->remise_percent).' %';
					}
					if ($objp->duration)
					{
						$optlabel .= " - ".$objp->duration;
						$outvallabel .= " - ".$objp->duration;
					}
					if (!$socid)
					{
						$optlabel .= " - ".dol_trunc($objp->name, 8);
						$outvallabel .= " - ".dol_trunc($objp->name, 8);
					}
					if ($objp->supplier_reputation)
					{
						  
						$reputations = array(''=>$langs->trans('Standard'), 'FAVORITE'=>$langs->trans('Favorite'), 'NOTTHGOOD'=>$langs->trans('NotTheGoodQualitySupplier'), 'DONOTORDER'=>$langs->trans('DoNotOrderThisProductToThisSupplier'));

						$optlabel .= " - ".$reputations[$objp->supplier_reputation];
						$outvallabel .= " - ".$reputations[$objp->supplier_reputation];
					}
				}
				else
				{
					if (empty($alsoproductwithnosupplierprice))       
					{
						$optlabel .= " - <span class='opacitymedium'>".$langs->trans("NoPriceDefinedForThisSupplier").'</span>';
						$outvallabel .= ' - '.$langs->transnoentities("NoPriceDefinedForThisSupplier");
					}
					else                                              
					{
						$optlabel .= " - <span class='opacitymedium'>".$langs->trans("NoPriceDefinedForThisSupplier").'</span>';
						$outvallabel .= ' - '.$langs->transnoentities("NoPriceDefinedForThisSupplier");
					}
				}

				$opt = '<option value="'.$outkey.'"';
				if ($selected && $selected == $objp->idprodfournprice) $opt .= ' selected';
				if (empty($objp->idprodfournprice) && empty($alsoproductwithnosupplierprice)) $opt .= ' disabled';
				if (!empty($objp->idprodfournprice) && $objp->idprodfournprice > 0)
				{
					$opt .= ' pbq="'.$objp->idprodfournprice.'" data-pbq="'.$objp->idprodfournprice.'" data-pbqqty="'.$objp->quantity.'" data-pbqup="'.$objp->unitprice.'" data-pbqpercent="'.$objp->remise_percent.'"';
				}
				$opt .= ' data-html="'.dol_escape_htmltag($optlabel).'"';
				$opt .= '>';

				$opt .= $optlabel;
				$outval .= $outvallabel;

				$opt .= "</option>\n";


				  
				  
				  
				$out .= $opt;
				array_push($outarray, array('key'=>$outkey, 'value'=>$outref, 'label'=>$outval, 'qty'=>$outqty, 'up'=>$objp->unitprice, 'discount'=>$outdiscount, 'type'=>$outtype, 'duration_value'=>$outdurationvalue, 'duration_unit'=>$outdurationunit, 'disabled'=>(empty($objp->idprodfournprice) ?true:false)));
				  
				  
				  
				  
				  
				  
				  
				  

				$i++;
			}
			$out .= '</select>';

			$this->db->free($result);

			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$out .= ajax_combobox($htmlname);

			if (empty($outputmode)) return $out;
			return $outarray;
		}
		else
		{
			dol_print_error($this->db);
		}
	}

      
	
    public function select_product_fourn_price($productid, $htmlname = 'productfournpriceid', $selected_supplier = '')
	{
          
		global $langs, $conf;

		$langs->load('stocks');

		$sql = "SELECT p.rowid, p.ref, p.label, p.price, p.duration, pfp.fk_soc,";
		$sql .= " pfp.ref_fourn, pfp.rowid as idprodfournprice, pfp.price as fprice, pfp.remise_percent, pfp.quantity, pfp.unitprice,";
		$sql .= " pfp.fk_supplier_price_expression, pfp.fk_product, pfp.tva_tx, s.nom as name";
		$sql .= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON pfp.fk_soc = s.rowid";
		$sql .= " WHERE pfp.entity IN (".getEntity('productsupplierprice').")";
		$sql .= " AND p.tobuy = 1";
		$sql .= " AND s.fournisseur = 1";
		$sql .= " AND p.rowid = ".$productid;
		$sql .= " ORDER BY s.nom, pfp.ref_fourn DESC";

		dol_syslog(get_class($this)."::select_product_fourn_price", LOG_DEBUG);
		$result = $this->db->query($sql);

		if ($result)
		{
			$num = $this->db->num_rows($result);

			$form = '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';

			if (!$num)
			{
				$form .= '<option value="0">-- '.$langs->trans("NoSupplierPriceDefinedForThisProduct").' --</option>';
			}
			else
			{
				require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';
				$form .= '<option value="0">&nbsp;</option>';

				$i = 0;
				while ($i < $num)
				{
					$objp = $this->db->fetch_object($result);

					$opt = '<option value="'.$objp->idprodfournprice.'"';
					  
					if ($num == 1 || ($selected_supplier > 0 && $objp->fk_soc == $selected_supplier)) {
						$opt .= ' selected';
					}
					$opt .= '>'.$objp->name.' - '.$objp->ref_fourn.' - ';

					if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_supplier_price_expression)) {
						$prod_supplier = new ProductFournisseur($this->db);
						$prod_supplier->product_fourn_price_id = $objp->idprodfournprice;
						$prod_supplier->id = $productid;
						$prod_supplier->fourn_qty = $objp->quantity;
						$prod_supplier->fourn_tva_tx = $objp->tva_tx;
						$prod_supplier->fk_supplier_price_expression = $objp->fk_supplier_price_expression;
						$priceparser = new PriceParser($this->db);
						$price_result = $priceparser->parseProductSupplier($prod_supplier);
						if ($price_result >= 0) {
							$objp->fprice = $price_result;
							if ($objp->quantity >= 1)
							{
								$objp->unitprice = $objp->fprice / $objp->quantity;
							}
						}
					}
					if ($objp->quantity == 1)
					{
						$opt .= price($objp->fprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/";
					}

					$opt .= $objp->quantity.' ';

					if ($objp->quantity == 1)
					{
						$opt .= $langs->trans("Unit");
					}
					else
					{
						$opt .= $langs->trans("Units");
					}
					if ($objp->quantity > 1)
					{
						$opt .= " - ";
						$opt .= price($objp->unitprice * (!empty($conf->global->DISPLAY_DISCOUNTED_SUPPLIER_PRICE) ? (1 - $objp->remise_percent / 100) : 1), 1, $langs, 0, 0, -1, $conf->currency)."/".$langs->trans("Unit");
					}
					if ($objp->duration) $opt .= " - ".$objp->duration;
					$opt .= "</option>\n";

					$form .= $opt;
					$i++;
				}
			}

			$form .= '</select>';
			$this->db->free($result);
			return $form;
		}
		else
		{
			dol_print_error($this->db);
		}
	}

      
	
    public function select_address($selected, $socid, $htmlname = 'address_id', $showempty = 0)
	{
          
		  
		$sql = "SELECT a.rowid, a.label";
		$sql .= " FROM ".MAIN_DB_PREFIX."societe_address as a";
		$sql .= " WHERE a.fk_soc = ".$socid;
		$sql .= " ORDER BY a.label ASC";

		dol_syslog(get_class($this)."::select_address", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			print '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';
			if ($showempty) print '<option value="0">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);

					if ($selected && $selected == $obj->rowid)
					{
						print '<option value="'.$obj->rowid.'" selected>'.$obj->label.'</option>';
					}
					else
					{
						print '<option value="'.$obj->rowid.'">'.$obj->label.'</option>';
					}
					$i++;
				}
			}
			print '</select>';
			return $num;
		}
		else
		{
			dol_print_error($this->db);
		}
	}


      
	/**
	 *      Load into cache list of payment terms
	 *
	 *      @return     int             Nb of lines loaded, <0 if KO
	 */
    public function load_cache_conditions_paiements()
	{
          
		global $langs;

		$num = count($this->cache_conditions_paiements);
		if ($num > 0) return 0;   

		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT rowid, code, libelle as label";
		$sql .= " FROM ".MAIN_DB_PREFIX.'c_payment_term';
		$sql .= " WHERE entity IN (".getEntity('c_payment_term').")";
		$sql .= " AND active > 0";
		$sql .= " ORDER BY sortorder";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				  
				$label = ($langs->trans("PaymentConditionShort".$obj->code) != ("PaymentConditionShort".$obj->code) ? $langs->trans("PaymentConditionShort".$obj->code) : ($obj->label != '-' ? $obj->label : ''));
				$this->cache_conditions_paiements[$obj->rowid]['code'] = $obj->code;
				$this->cache_conditions_paiements[$obj->rowid]['label'] = $label;
				$i++;
			}

			  

			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

      
	
    public function load_cache_availability()
	{
          
		global $langs;

		$num = count($this->cache_availability);
		if ($num > 0) return 0;   

		dol_syslog(__METHOD__, LOG_DEBUG);

		$langs->load('propal');

		$sql = "SELECT rowid, code, label";
		$sql .= " FROM ".MAIN_DB_PREFIX.'c_availability';
		$sql .= " WHERE active > 0";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				  
				$label = ($langs->trans("AvailabilityType".$obj->code) != ("AvailabilityType".$obj->code) ? $langs->trans("AvailabilityType".$obj->code) : ($obj->label != '-' ? $obj->label : ''));
				$this->cache_availability[$obj->rowid]['code'] = $obj->code;
				$this->cache_availability[$obj->rowid]['label'] = $label;
				$i++;
			}

			$this->cache_availability = dol_sort_array($this->cache_availability, 'label', 'asc', 0, 0, 1);

			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	
    public function selectAvailabilityDelay($selected = '', $htmlname = 'availid', $filtertype = '', $addempty = 0)
	{
		global $langs, $user;

		$this->load_cache_availability();

		dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

		print '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'">';
		if ($addempty) print '<option value="0">&nbsp;</option>';
		foreach ($this->cache_availability as $id => $arrayavailability)
		{
			if ($selected == $id)
			{
				print '<option value="'.$id.'" selected>';
			}
			else
			{
				print '<option value="'.$id.'">';
			}
			print $arrayavailability['label'];
			print '</option>';
		}
		print '</select>';
		if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}

	
    public function loadCacheInputReason()
	{
		global $langs;

		$num = count($this->cache_demand_reason);
		if ($num > 0) return 0;   

		$sql = "SELECT rowid, code, label";
		$sql .= " FROM ".MAIN_DB_PREFIX.'c_input_reason';
		$sql .= " WHERE active > 0";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			$tmparray = array();
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				  
				$label = ($obj->label != '-' ? $obj->label : '');
				if ($langs->trans("DemandReasonType".$obj->code) != ("DemandReasonType".$obj->code)) $label = $langs->trans("DemandReasonType".$obj->code);   
				if ($langs->trans($obj->code) != $obj->code) $label = $langs->trans($obj->code);   

				$tmparray[$obj->rowid]['id']   = $obj->rowid;
				$tmparray[$obj->rowid]['code'] = $obj->code;
				$tmparray[$obj->rowid]['label'] = $label;
				$i++;
			}

			$this->cache_demand_reason = dol_sort_array($tmparray, 'label', 'asc', 0, 0, 1);

			unset($tmparray);
			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	
    public function selectInputReason($selected = '', $htmlname = 'demandreasonid', $exclude = '', $addempty = 0)
	{
		global $langs, $user;

		$this->loadCacheInputReason();

		print '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';
		if ($addempty) print '<option value="0"'.(empty($selected) ? ' selected' : '').'>&nbsp;</option>';
		foreach ($this->cache_demand_reason as $id => $arraydemandreason)
		{
			if ($arraydemandreason['code'] == $exclude) continue;

			if ($selected && ($selected == $arraydemandreason['id'] || $selected == $arraydemandreason['code']))
			{
				print '<option value="'.$arraydemandreason['id'].'" selected>';
			}
			else
			{
				print '<option value="'.$arraydemandreason['id'].'">';
			}
			$label = $arraydemandreason['label'];   
			print $langs->trans($label);
			print '</option>';
		}
		print '</select>';
		if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}

      
	
    public function load_cache_types_paiements()
	{
          
		global $langs;

		$num = count($this->cache_types_paiements);
		if ($num > 0) return $num;   

		dol_syslog(__METHOD__, LOG_DEBUG);

		$this->cache_types_paiements = array();

		$sql = "SELECT id, code, libelle as label, type, active";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_paiement";
		$sql .= " WHERE entity IN (".getEntity('c_paiement').")";
		  

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				  
				$label = ($langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code) != ("PaymentTypeShort".$obj->code) ? $langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code) : ($obj->label != '-' ? $obj->label : ''));
				$this->cache_types_paiements[$obj->id]['id'] = $obj->id;
				$this->cache_types_paiements[$obj->id]['code'] = $obj->code;
				$this->cache_types_paiements[$obj->id]['label'] = $label;
				$this->cache_types_paiements[$obj->id]['type'] = $obj->type;
				$this->cache_types_paiements[$obj->id]['active'] = $obj->active;
				$i++;
			}

			$this->cache_types_paiements = dol_sort_array($this->cache_types_paiements, 'label', 'asc', 0, 0, 1);

			return $num;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}


      
	
    public function select_conditions_paiements($selected = 0, $htmlname = 'condid', $filtertype = -1, $addempty = 0, $noinfoadmin = 0, $morecss = '')
	{
          
		global $langs, $user, $conf;

		dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

		$this->load_cache_conditions_paiements();

		  
		if (empty($selected) && !empty($conf->global->MAIN_DEFAULT_PAYMENT_TERM_ID)) $selected = $conf->global->MAIN_DEFAULT_PAYMENT_TERM_ID;

		print '<select id="'.$htmlname.'" class="flat selectpaymentterms'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'">';
		if ($addempty) print '<option value="0">&nbsp;</option>';
		foreach ($this->cache_conditions_paiements as $id => $arrayconditions)
		{
			if ($selected == $id)
			{
				print '<option value="'.$id.'" selected>';
			}
			else
			{
				print '<option value="'.$id.'">';
			}
			print $arrayconditions['label'];
			print '</option>';
		}
		print '</select>';
		if ($user->admin && empty($noinfoadmin)) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}


      
	
    public function select_types_paiements($selected = '', $htmlname = 'paiementtype', $filtertype = '', $format = 0, $empty = 1, $noadmininfo = 0, $maxlength = 0, $active = 1, $morecss = '')
	{
          
		global $langs, $user;

		dol_syslog(__METHOD__." ".$selected.", ".$htmlname.", ".$filtertype.", ".$format, LOG_DEBUG);

		$filterarray = array();
		if ($filtertype == 'CRDT')  	$filterarray = array(0, 2, 3);
		elseif ($filtertype == 'DBIT') 	$filterarray = array(1, 2, 3);
		elseif ($filtertype != '' && $filtertype != '-1') $filterarray = explode(',', $filtertype);

		$this->load_cache_types_paiements();

		print '<select id="select'.$htmlname.'" class="flat selectpaymenttypes'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'">';
		if ($empty) print '<option value="">&nbsp;</option>';
		foreach ($this->cache_types_paiements as $id => $arraytypes)
		{
			  
			if ($active >= 0 && $arraytypes['active'] != $active) continue;

			  
			if (count($filterarray) && !in_array($arraytypes['type'], $filterarray)) continue;

			  
			if ($empty && empty($arraytypes['code'])) continue;

			if ($format == 0) print '<option value="'.$id.'"';
			elseif ($format == 1) print '<option value="'.$arraytypes['code'].'"';
			elseif ($format == 2) print '<option value="'.$arraytypes['code'].'"';
			elseif ($format == 3) print '<option value="'.$id.'"';
			  
			if ($format==1 || $format==2) {
				if ($selected == $arraytypes['code']) print ' selected';
			} else {
				if ($selected == $id) print ' selected';
			}
			print '>';
			if ($format == 0) $value = ($maxlength ?dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
			elseif ($format == 1) $value = $arraytypes['code'];
			elseif ($format == 2) $value = ($maxlength ?dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
			elseif ($format == 3) $value = $arraytypes['code'];
			print $value ? $value : '&nbsp;';
			print '</option>';
		}
		print '</select>';
		if ($user->admin && !$noadmininfo) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}


	
    public function selectPriceBaseType($selected = '', $htmlname = 'price_base_type')
	{
		global $langs;

		$return = '';

		$return .= '<select class="flat maxwidth75" id="select_'.$htmlname.'" name="'.$htmlname.'">';
		$options = array(
			'HT'=>$langs->trans("HT"),
			'TTC'=>$langs->trans("TTC")
		);
		foreach ($options as $id => $value)
		{
			if ($selected == $id)
			{
				$return .= '<option value="'.$id.'" selected>'.$value;
			}
			else
			{
				$return .= '<option value="'.$id.'">'.$value;
			}
			$return .= '</option>';
		}
		$return .= '</select>';

		return $return;
	}

	
    public function selectShippingMethod($selected = '', $htmlname = 'shipping_method_id', $filtre = '', $useempty = 0, $moreattrib = '')
	{
		global $langs, $conf, $user;

		$langs->load("admin");
		$langs->load("deliveries");

		$sql = "SELECT rowid, code, libelle as label";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_shipment_mode";
		$sql .= " WHERE active > 0";
		if ($filtre) $sql .= " AND ".$filtre;
		$sql .= " ORDER BY libelle ASC";

		dol_syslog(get_class($this)."::selectShippingMode", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num) {
				print '<select id="select'.$htmlname.'" class="flat selectshippingmethod" name="'.$htmlname.'"'.($moreattrib ? ' '.$moreattrib : '').'>';
				if ($useempty == 1 || ($useempty == 2 && $num > 1)) {
					print '<option value="-1">&nbsp;</option>';
				}
				while ($i < $num) {
					$obj = $this->db->fetch_object($result);
					if ($selected == $obj->rowid) {
						print '<option value="'.$obj->rowid.'" selected>';
					} else {
						print '<option value="'.$obj->rowid.'">';
					}
					print ($langs->trans("SendingMethod".strtoupper($obj->code)) != "SendingMethod".strtoupper($obj->code)) ? $langs->trans("SendingMethod".strtoupper($obj->code)) : $obj->label;
					print '</option>';
					$i++;
				}
				print "</select>";
				if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			} else {
				print $langs->trans("NoShippingMethodDefined");
			}
		} else {
			dol_print_error($this->db);
		}
	}

	
    public function formSelectShippingMethod($page, $selected = '', $htmlname = 'shipping_method_id', $addempty = 0)
	{
		global $langs, $db;

		$langs->load("deliveries");

		if ($htmlname != "none") {
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setshippingmethod">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$this->selectShippingMethod($selected, $htmlname, '', $addempty);
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		} else {
			if ($selected) {
				$code = $langs->getLabelFromKey($db, $selected, 'c_shipment_mode', 'rowid', 'code');
				print $langs->trans("SendingMethod".strtoupper($code));
			} else {
				print "&nbsp;";
			}
		}
	}

	/**
	 * Creates HTML last in cycle situation invoices selector
	 *
	 * @param     string  $selected   		Preselected ID
	 * @param     int     $socid      		Company ID
	 *
	 * @return    string                     HTML select
	 */
    public function selectSituationInvoices($selected = '', $socid = 0)
	{
		global $langs;

		$langs->load('bills');

		$opt = '<option value ="" selected></option>';
		$sql = 'SELECT rowid, ref, situation_cycle_ref, situation_counter, situation_final, fk_soc';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'facture';
		$sql .= ' WHERE entity IN ('.getEntity('invoice').')';
		$sql .= ' AND situation_counter>=1';
		$sql .= ' ORDER by situation_cycle_ref, situation_counter desc';
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql) > 0) {
			  
			$ref = 0;
			while ($obj = $this->db->fetch_object($resql)) {
				  
			    if ($socid == $obj->fk_soc) {
					  
			        if ($obj->situation_cycle_ref != $ref) {
						  
			            $ref = $obj->situation_cycle_ref;
						  
			            if ($obj->situation_final != 1) {
							  
			                if (substr($obj->ref, 1, 4) != 'PROV') {
			                    if ($selected == $obj->rowid) {
			                        $opt .= '<option value="'.$obj->rowid.'" selected>'.$obj->ref.'</option>';
								} else {
								    $opt .= '<option value="'.$obj->rowid.'">'.$obj->ref.'</option>';
								}
							}
						}
					}
				}
			}
		}
		else
		{
				dol_syslog("Error sql=".$sql.", error=".$this->error, LOG_ERR);
		}
		if ($opt == '<option value ="" selected></option>')
		{
			$opt = '<option value ="0" selected>'.$langs->trans('NoSituations').'</option>';
		}
		return $opt;
	}

	/**
	 *      Creates HTML units selector (code => label)
	 *
	 *      @param	string	$selected       Preselected Unit ID
	 *      @param  string	$htmlname       Select name
	 *      @param	int		$showempty		Add a nempty line
	 * 		@return	string                  HTML select
	 */
    public function selectUnits($selected = '', $htmlname = 'units', $showempty = 0)
	{
		global $langs;

		$langs->load('products');

		$return = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';

		$sql = 'SELECT rowid, label, code from '.MAIN_DB_PREFIX.'c_units';
		$sql .= ' WHERE active > 0';

		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql) > 0)
		{
			if ($showempty) $return .= '<option value="none"></option>';

			while ($res = $this->db->fetch_object($resql))
			{
			    $unitLabel = $res->label;
			    if (!empty($langs->tab_translate['unit'.$res->code]))	  
			    {
			        $unitLabel = $langs->trans('unit'.$res->code) != $res->label ? $langs->trans('unit'.$res->code) : $res->label;
			    }

				if ($selected == $res->rowid)
				{
				    $return .= '<option value="'.$res->rowid.'" selected>'.$unitLabel.'</option>';
				}
				else
				{
				    $return .= '<option value="'.$res->rowid.'">'.$unitLabel.'</option>';
				}
			}
			$return .= '</select>';
		}
		return $return;
	}

      
	
    public function select_comptes($selected = '', $htmlname = 'accountid', $status = 0, $filtre = '', $useempty = 0, $moreattrib = '', $showcurrency = 0, $morecss = '')
	{
          
		global $langs, $conf;

		$langs->load("admin");
		$num = 0;

		$sql = "SELECT rowid, label, bank, clos as status, currency_code";
		$sql .= " FROM ".MAIN_DB_PREFIX."bank_account";
		$sql .= " WHERE entity IN (".getEntity('bank_account').")";
		if ($status != 2) $sql .= " AND clos = ".(int) $status;
		if ($filtre) $sql .= " AND ".$filtre;
		$sql .= " ORDER BY label";

		dol_syslog(get_class($this)."::select_comptes", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num)
			{
				print '<select id="select'.$htmlname.'" class="flat selectbankaccount'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'"'.($moreattrib ? ' '.$moreattrib : '').'>';
				if ($useempty == 1 || ($useempty == 2 && $num > 1))
				{
					print '<option value="-1">&nbsp;</option>';
				}

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($result);
					if ($selected == $obj->rowid)
					{
						print '<option value="'.$obj->rowid.'" selected>';
					}
					else
					{
						print '<option value="'.$obj->rowid.'">';
					}
					print trim($obj->label);
					if ($showcurrency) print ' ('.$obj->currency_code.')';
					if ($status == 2 && $obj->status == 1) print ' ('.$langs->trans("Closed").')';
					print '</option>';
					$i++;
				}
				print "</select>";
			}
			else
			{
				if ($status == 0) print '<span class="opacitymedium">'.$langs->trans("NoActiveBankAccountDefined").'</span>';
				else print '<span class="opacitymedium">'.$langs->trans("NoBankAccountFound").'</span>';
			}
		}
		else {
			dol_print_error($this->db);
		}

		return $num;
	}

	
	public function selectEstablishments($selected = '', $htmlname = 'entity', $status = 0, $filtre = '', $useempty = 0, $moreattrib = '')
	{
          
		global $langs, $conf;

		$langs->load("admin");
		$num = 0;

		$sql = "SELECT rowid, name, fk_country, status, entity";
		$sql .= " FROM ".MAIN_DB_PREFIX."establishment";
		$sql .= " WHERE 1=1";
		if ($status != 2) $sql .= " AND status = ".(int) $status;
		if ($filtre) $sql .= " AND ".$filtre;
		$sql .= " ORDER BY name";

		dol_syslog(get_class($this)."::select_establishment", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num)
			{
				print '<select id="select'.$htmlname.'" class="flat selectestablishment" name="'.$htmlname.'"'.($moreattrib ? ' '.$moreattrib : '').'>';
				if ($useempty == 1 || ($useempty == 2 && $num > 1))
				{
					print '<option value="-1">&nbsp;</option>';
				}

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($result);
					if ($selected == $obj->rowid)
					{
						print '<option value="'.$obj->rowid.'" selected>';
					}
					else
					{
						print '<option value="'.$obj->rowid.'">';
					}
					print trim($obj->name);
					if ($status == 2 && $obj->status == 1) print ' ('.$langs->trans("Closed").')';
					print '</option>';
					$i++;
				}
				print "</select>";
			}
			else
			{
				if ($status == 0) print '<span class="opacitymedium">'.$langs->trans("NoActiveEstablishmentDefined").'</span>';
				else print '<span class="opacitymedium">'.$langs->trans("NoEstablishmentFound").'</span>';
			}
		}
		else {
			dol_print_error($this->db);
		}
	}

	/**
	 *    Display form to select bank account
	 *
	 *    @param	string	$page        Page
	 *    @param    int		$selected    Id of bank account
	 *    @param    string	$htmlname    Name of select html field
	 *    @param    int		$addempty    1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
	 *    @return	void
	 */
    public function formSelectAccount($page, $selected = '', $htmlname = 'fk_account', $addempty = 0)
	{
		global $langs;
		if ($htmlname != "none") {
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setbankaccount">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$nbaccountfound = $this->select_comptes($selected, $htmlname, 0, '', $addempty);
			if ($nbaccountfound > 0) print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		} else {
			$langs->load('banks');

			if ($selected) {
				require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
				$bankstatic = new Account($this->db);
				$result = $bankstatic->fetch($selected);
				if ($result) print $bankstatic->getNomUrl(1);
			} else {
				print "&nbsp;";
			}
		}
	}

      
	
    public function select_all_categories($type, $selected = '', $htmlname = "parent", $maxlength = 64, $markafterid = 0, $outputmode = 0, $include = 0, $morecss = '')
	{
          
		global $conf, $langs;
		$langs->load("categories");

		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		  
		if (is_numeric($type))
		{
			dol_syslog(__METHOD__.': using numeric value for parameter type is deprecated. Use string code instead.', LOG_WARNING);
		}

		if ($type === Categorie::TYPE_BANK_LINE)
		{
			  
			$cate_arbo = array();
			$sql = "SELECT c.label, c.rowid";
			$sql .= " FROM ".MAIN_DB_PREFIX."bank_categ as c";
			$sql .= " WHERE entity = ".$conf->entity;
			$sql .= " ORDER BY c.label";
			$result = $this->db->query($sql);
			if ($result)
			{
				$num = $this->db->num_rows($result);
				$i = 0;
				while ($i < $num)
				{
					$objp = $this->db->fetch_object($result);
					if ($objp) $cate_arbo[$objp->rowid] = array('id'=>$objp->rowid, 'fulllabel'=>$objp->label);
					$i++;
				}
				$this->db->free($result);
			}
			else dol_print_error($this->db);
		}
		else
		{
			$cat = new Categorie($this->db);
            $cate_arbo = $cat->get_full_arbo($type, $markafterid, $include);
		}

		$output = '<select class="flat'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'" id="'.$htmlname.'">';
		$outarray = array();
		if (is_array($cate_arbo))
		{
			if (!count($cate_arbo)) $output .= '<option value="-1" disabled>'.$langs->trans("NoCategoriesDefined").'</option>';
			else
			{
				$output .= '<option value="-1">&nbsp;</option>';
				foreach ($cate_arbo as $key => $value)
				{
					if ($cate_arbo[$key]['id'] == $selected || ($selected == 'auto' && count($cate_arbo) == 1))
					{
						$add = 'selected ';
					}
					else
					{
						$add = '';
					}
					$output .= '<option '.$add.'value="'.$cate_arbo[$key]['id'].'">'.dol_trunc($cate_arbo[$key]['fulllabel'], $maxlength, 'middle').'</option>';

					$outarray[$cate_arbo[$key]['id']] = $cate_arbo[$key]['fulllabel'];
				}
			}
		}
		$output .= '</select>';
		$output .= "\n";

		if ($outputmode) return $outarray;
		return $output;
	}

      
	
    public function form_confirm($page, $title, $question, $action, $formquestion = '', $selectedchoice = "", $useajax = 0, $height = 170, $width = 500)
	{
          
        dol_syslog(__METHOD__.': using form_confirm is deprecated. Use formconfim instead.', LOG_WARNING);
		print $this->formconfirm($page, $title, $question, $action, $formquestion, $selectedchoice, $useajax, $height, $width);
	}

	
    public function formconfirm($page, $title, $question, $action, $formquestion = '', $selectedchoice = '', $useajax = 0, $height = 0, $width = 500, $disableformtag = 0)
	{
		global $langs, $conf;
		global $useglobalvars;

		$more = '<!-- formconfirm -->';
		$formconfirm = '';
		$inputok = array();
		$inputko = array();

		  
		$newselectedchoice = empty($selectedchoice) ? "no" : $selectedchoice;
		if ($conf->browser->layout == 'phone') $width = '95%';

		  
		if (empty($height)) {
			$height = 210;
			if (is_array($formquestion) && count($formquestion) > 2) {
				$height += ((count($formquestion) - 2) * 24);
			}
		}

		if (is_array($formquestion) && !empty($formquestion))
		{
			  
			foreach ($formquestion as $key => $input)
			{
				if (is_array($input) && !empty($input))
				{
					if ($input['type'] == 'hidden')
					{
						$more .= '<input type="hidden" id="'.$input['name'].'" name="'.$input['name'].'" value="'.dol_escape_htmltag($input['value']).'">'."\n";
					}
				}
			}

			  
			$moreonecolumn = '';
			$more .= '<div class="tagtable paddingtopbottomonly centpercent noborderspacing">'."\n";
			foreach ($formquestion as $key => $input)
			{
				if (is_array($input) && !empty($input))
				{
					$size = (!empty($input['size']) ? ' size="'.$input['size'].'"' : '');
					$moreattr = (!empty($input['moreattr']) ? ' '.$input['moreattr'] : '');
					$morecss = (!empty($input['morecss']) ? ' '.$input['morecss'] : '');

					if ($input['type'] == 'text')
					{
						$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div><div class="tagtd"><input type="text" class="flat'.$morecss.'" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'"'.$moreattr.' /></div></div>'."\n";
					}
					elseif ($input['type'] == 'password')
					{
						$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div><div class="tagtd"><input type="password" class="flat'.$morecss.'" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'"'.$moreattr.' /></div></div>'."\n";
					}
					elseif ($input['type'] == 'select')
					{
						$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">';
						if (!empty($input['label'])) $more .= $input['label'].'</div><div class="tagtd tdtop left">';
						$more .= $this->selectarray($input['name'], $input['values'], $input['default'], 1, 0, 0, $moreattr, 0, 0, 0, '', $morecss);
						$more .= '</div></div>'."\n";
					}
					elseif ($input['type'] == 'checkbox')
					{
						$more .= '<div class="tagtr">';
						$more .= '<div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].' </div><div class="tagtd">';
						$more .= '<input type="checkbox" class="flat'.$morecss.'" id="'.$input['name'].'" name="'.$input['name'].'"'.$moreattr;
						if (!is_bool($input['value']) && $input['value'] != 'false' && $input['value'] != '0') $more .= ' checked';
						if (is_bool($input['value']) && $input['value']) $more .= ' checked';
						if (isset($input['disabled'])) $more .= ' disabled';
						$more .= ' /></div>';
						$more .= '</div>'."\n";
					}
					elseif ($input['type'] == 'radio')
					{
						$i = 0;
						foreach ($input['values'] as $selkey => $selval)
						{
							$more .= '<div class="tagtr">';
							if ($i == 0) $more .= '<div class="tagtd'.(empty($input['tdclass']) ? ' tdtop' : (' tdtop '.$input['tdclass'])).'">'.$input['label'].'</div>';
							else $more .= '<div clas="tagtd'.(empty($input['tdclass']) ? '' : (' "'.$input['tdclass'])).'">&nbsp;</div>';
							$more .= '<div class="tagtd"><input type="radio" class="flat'.$morecss.'" id="'.$input['name'].'" name="'.$input['name'].'" value="'.$selkey.'"'.$moreattr;
							if ($input['disabled']) $more .= ' disabled';
							$more .= ' /> ';
							$more .= $selval;
							$more .= '</div></div>'."\n";
							$i++;
						}
					}
					elseif ($input['type'] == 'date')
					{
						$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div>';
						$more .= '<div class="tagtd">';
						$more .= $this->selectDate($input['value'], $input['name'], 0, 0, 0, '', 1, 0);
						$more .= '</div></div>'."\n";
						$formquestion[] = array('name'=>$input['name'].'day');
						$formquestion[] = array('name'=>$input['name'].'month');
						$formquestion[] = array('name'=>$input['name'].'year');
						$formquestion[] = array('name'=>$input['name'].'hour');
						$formquestion[] = array('name'=>$input['name'].'min');
					}
					elseif ($input['type'] == 'other')
					{
						$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">';
						if (!empty($input['label'])) $more .= $input['label'].'</div><div class="tagtd">';
						$more .= $input['value'];
						$more .= '</div></div>'."\n";
					}

					elseif ($input['type'] == 'onecolumn')
					{
						$moreonecolumn .= '<div class="margintoponly">';
						$moreonecolumn .= $input['value'];
						$moreonecolumn .= '</div>'."\n";
					}
				}
			}
			$more .= '</div>'."\n";
			$more .= $moreonecolumn;
		}

		  
		  
		  
		if (!empty($conf->dol_use_jmobile)) $useajax = 0;
		if (empty($conf->use_javascript_ajax)) $useajax = 0;

		if ($useajax)
		{
			$autoOpen = true;
			$dialogconfirm = 'dialog-confirm';
			$button = '';
			if (!is_numeric($useajax))
			{
				$button = $useajax;
				$useajax = 1;
				$autoOpen = false;
				$dialogconfirm .= '-'.$button;
			}
			$pageyes = $page.(preg_match('/\?/', $page) ? '&' : '?').'action='.$action.'&confirm=yes';
			$pageno = ($useajax == 2 ? $page.(preg_match('/\?/', $page) ? '&' : '?').'confirm=no' : '');
			  
			if (is_array($formquestion))
			{
				foreach ($formquestion as $key => $input)
				{
					  
					if (is_array($input) && isset($input['name'])) array_push($inputok, $input['name']);
					if (isset($input['inputko']) && $input['inputko'] == 1) array_push($inputko, $input['name']);
				}
			}
			  
			$formconfirm .= '<div id="'.$dialogconfirm.'" title="'.dol_escape_htmltag($title).'" style="display: none;">';
			if (!empty($formquestion['text'])) {
				$formconfirm .= '<div class="confirmtext">'.$formquestion['text'].'</div>'."\n";
			}
			if (!empty($more)) {
				$formconfirm .= '<div class="confirmquestions">'.$more.'</div>'."\n";
			}
			$formconfirm .= ($question ? '<div class="confirmmessage">'.img_help('', '').' '.$question.'</div>' : '');
			$formconfirm .= '</div>'."\n";

			$formconfirm .= "\n<!-- begin ajax formconfirm page=".$page." -->\n";
			$formconfirm .= '<script type="text/javascript">'."\n";
			$formconfirm .= 'jQuery(document).ready(function() {
            $(function() {
            	$( "#'.$dialogconfirm.'" ).dialog(
            	{
                    autoOpen: '.($autoOpen ? "true" : "false").',';
			if ($newselectedchoice == 'no')
			{
				$formconfirm .= '
						open: function() {
            				$(this).parent().find("button.ui-button:eq(2)").focus();
						},';
			}
			$formconfirm .= '
                    resizable: false,
                    height: "'.$height.'",
                    width: "'.$width.'",
                    modal: true,
                    closeOnEscape: false,
                    buttons: {
                        "'.dol_escape_js($langs->transnoentities("Yes")).'": function() {
                        	var options = "&token='.urlencode(newToken()).'";
                        	var inputok = '.json_encode($inputok).';
                         	var pageyes = "'.dol_escape_js(!empty($pageyes) ? $pageyes : '').'";
                         	if (inputok.length>0) {
                         		$.each(inputok, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         		    if ($("#" + inputname).attr("type") == "radio") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + encodeURIComponent(inputvalue);
                         		});
                         	}
                         	var urljump = pageyes + (pageyes.indexOf("?") < 0 ? "?" : "") + options;
                         	  
            				if (pageyes.length > 0) { location.href = urljump; }
                            $(this).dialog("close");
                        },
                        "'.dol_escape_js($langs->transnoentities("No")).'": function() {
                        	var options = "&token='.urlencode(newToken()).'";
                         	var inputko = '.json_encode($inputko).';
                         	var pageno="'.dol_escape_js(!empty($pageno) ? $pageno : '').'";
                         	if (inputko.length>0) {
                         		$.each(inputko, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + encodeURIComponent(inputvalue);
                         		});
                         	}
                         	var urljump=pageno + (pageno.indexOf("?") < 0 ? "?" : "") + options;
                         	  
            				if (pageno.length > 0) { location.href = urljump; }
                            $(this).dialog("close");
                        }
                    }
                }
                );

            	var button = "'.$button.'";
            	if (button.length > 0) {
                	$( "#" + button ).click(function() {
                		$("#'.$dialogconfirm.'").dialog("open");
        			});
                }
            });
            });
            </script>';
			$formconfirm .= "<!-- end ajax formconfirm -->\n";
		}
		else
		{
			$formconfirm .= "\n<!-- begin formconfirm page=".$page." -->\n";

			if (empty($disableformtag)) $formconfirm .= '<form method="POST" action="'.$page.'" class="notoptoleftroright">'."\n";

			$formconfirm .= '<input type="hidden" name="action" value="'.$action.'">'."\n";
			$formconfirm .= '<input type="hidden" name="token" value="'.newToken().'">'."\n";

			$formconfirm .= '<table class="valid centpercent">'."\n";

			  
			$formconfirm .= '<tr class="validtitre"><td class="validtitre" colspan="3">'.img_picto('', 'recent').' '.$title.'</td></tr>'."\n";

			  
			if (!empty($formquestion['text'])) {
				$formconfirm .= '<tr class="valid"><td class="valid" colspan="3">'.$formquestion['text'].'</td></tr>'."\n";
			}

			  
			if ($more)
			{
				$formconfirm .= '<tr class="valid"><td class="valid" colspan="3">'."\n";
				$formconfirm .= $more;
				$formconfirm .= '</td></tr>'."\n";
			}

			  
			$formconfirm .= '<tr class="valid">';
			$formconfirm .= '<td class="valid">'.$question.'</td>';
			$formconfirm .= '<td class="valid">';
			$formconfirm .= $this->selectyesno("confirm", $newselectedchoice);
			$formconfirm .= '</td>';
			$formconfirm .= '<td class="valid center"><input class="button valignmiddle" type="submit" value="'.$langs->trans("Validate").'"></td>';
			$formconfirm .= '</tr>'."\n";

			$formconfirm .= '</table>'."\n";

			if (empty($disableformtag)) $formconfirm .= "</form>\n";
			$formconfirm .= '<br>';

			$formconfirm .= "<!-- end formconfirm -->\n";
		}

		return $formconfirm;
	}


      
	
    public function form_project($page, $socid, $selected = '', $htmlname = 'projectid', $discard_closed = 0, $maxlength = 20, $forcefocus = 0, $nooutput = 0)
	{
          
		global $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

		$out = '';

		$formproject = new FormProjets($this->db);

		$langs->load("project");
		if ($htmlname != "none")
		{
			$out .= "\n";
			$out .= '<form method="post" action="'.$page.'">';
			$out .= '<input type="hidden" name="action" value="classin">';
			$out .= '<input type="hidden" name="token" value="'.newToken().'">';
			$out .= $formproject->select_projects($socid, $selected, $htmlname, $maxlength, 0, 1, $discard_closed, $forcefocus, 0, 0, '', 1);
			$out .= '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
			$out .= '</form>';
		}
		else
		{
			if ($selected)
			{
				$projet = new Project($this->db);
				$projet->fetch($selected);
				  
				$out .= $projet->getNomUrl(0, '', 1);
			}
			else
			{
				$out .= "&nbsp;";
			}
		}

		if (empty($nooutput))
		{
			print $out;
			return '';
		}
		return $out;
	}

      
	
    public function form_conditions_reglement($page, $selected = '', $htmlname = 'cond_reglement_id', $addempty = 0)
	{
          
		global $langs;
		if ($htmlname != "none")
		{
			print '<form method="post" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setconditions">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$this->select_conditions_paiements($selected, $htmlname, -1, $addempty);
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				$this->load_cache_conditions_paiements();
				print $this->cache_conditions_paiements[$selected]['label'];
			} else {
				print "&nbsp;";
			}
		}
	}

      
	
    public function form_availability($page, $selected = '', $htmlname = 'availability', $addempty = 0)
	{
          
		global $langs;
		if ($htmlname != "none")
		{
			print '<form method="post" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setavailability">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$this->selectAvailabilityDelay($selected, $htmlname, -1, $addempty);
			print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				$this->load_cache_availability();
				print $this->cache_availability[$selected]['label'];
			} else {
				print "&nbsp;";
			}
		}
	}

	
    public function formInputReason($page, $selected = '', $htmlname = 'demandreason', $addempty = 0)
    {
		global $langs;
		if ($htmlname != "none")
		{
			print '<form method="post" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setdemandreason">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$this->selectInputReason($selected, $htmlname, -1, $addempty);
			print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				$this->loadCacheInputReason();
				foreach ($this->cache_demand_reason as $key => $val)
				{
					if ($val['id'] == $selected)
					{
						print $val['label'];
						break;
					}
				}
			} else {
				print "&nbsp;";
			}
		}
	}

      
	
    public function form_date($page, $selected, $htmlname, $displayhour = 0, $displaymin = 0, $nooutput = 0)
	{
          
		global $langs;

		$ret = '';

		if ($htmlname != "none")
		{
			$ret .= '<form method="post" action="'.$page.'" name="form'.$htmlname.'">';
			$ret .= '<input type="hidden" name="action" value="set'.$htmlname.'">';
			$ret .= '<input type="hidden" name="token" value="'.newToken().'">';
			$ret .= '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
			$ret .= '<tr><td>';
			$ret .= $this->selectDate($selected, $htmlname, $displayhour, $displaymin, 1, 'form'.$htmlname, 1, 0);
			$ret .= '</td>';
			$ret .= '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
			$ret .= '</tr></table></form>';
		}
		else
		{
			if ($displayhour) $ret .= dol_print_date($selected, 'dayhour');
			else $ret .= dol_print_date($selected, 'day');
		}

		if (empty($nooutput)) print $ret;
		return $ret;
	}


      
	
    public function form_users($page, $selected = '', $htmlname = 'userid', $exclude = '', $include = '')
	{
          
		global $langs;

		if ($htmlname != "none")
		{
			print '<form method="POST" action="'.$page.'" name="form'.$htmlname.'">';
			print '<input type="hidden" name="action" value="set'.$htmlname.'">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print $this->select_dolusers($selected, $htmlname, 1, $exclude, 0, $include);
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
				$theuser = new User($this->db);
				$theuser->fetch($selected);
				print $theuser->getNomUrl(1);
			} else {
				print "&nbsp;";
			}
		}
	}


      
	
    public function form_modes_reglement($page, $selected = '', $htmlname = 'mode_reglement_id', $filtertype = '', $active = 1, $addempty = 0)
	{
          
		global $langs;
		if ($htmlname != "none")
		{
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setmode">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			$this->select_types_paiements($selected, $htmlname, $filtertype, 0, $addempty, 0, 0, $active);
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				$this->load_cache_types_paiements();
				print $this->cache_types_paiements[$selected]['label'];
			} else {
				print "&nbsp;";
			}
		}
	}

      
	
    public function form_multicurrency_code($page, $selected = '', $htmlname = 'multicurrency_code')
	{
          
		global $langs;
		if ($htmlname != "none")
		{
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setmulticurrencycode">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print $this->selectMultiCurrency($selected, $htmlname, 0);
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			dol_include_once('/core/lib/company.lib.php');
			print !empty($selected) ? currency_name($selected, 1) : '&nbsp;';
		}
	}

      
	
    public function form_multicurrency_rate($page, $rate = '', $htmlname = 'multicurrency_tx', $currency = '')
	{
          
		global $langs, $mysoc, $conf;

		if ($htmlname != "none")
		{
			print '<form method="POST" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setmulticurrencyrate">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="text" name="'.$htmlname.'" value="'.(!empty($rate) ? price($rate) : 1).'" size="10" /> ';
			print '<select name="calculation_mode">';
			print '<option value="1">'.$currency.' > '.$conf->currency.'</option>';
			print '<option value="2">'.$conf->currency.' > '.$currency.'</option>';
			print '</select> ';
			print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			print '</form>';
		}
		else
		{
			if (!empty($rate))
			{
				print price($rate, 1, $langs, 1, 0);
				if ($currency && $rate != 1) print ' &nbsp; ('.price($rate, 1, $langs, 1, 0).' '.$currency.' = 1 '.$conf->currency.')';
			}
			else
			{
				print 1;
			}
		}
	}


      
	
    public function form_remise_dispo($page, $selected, $htmlname, $socid, $amount, $filter = '', $maxvalue = 0, $more = '', $hidelist = 0, $discount_type = 0)
	{
          
		global $conf, $langs;
		if ($htmlname != "none")
		{
			print '<form method="post" action="'.$page.'">';
			print '<input type="hidden" name="action" value="setabsolutediscount">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<div class="inline-block">';
			if (!empty($discount_type)) {
				if (!empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS))
				{
					if (!$filter || $filter == "fk_invoice_supplier_source IS NULL") $translationKey = 'HasAbsoluteDiscountFromSupplier';   
					else $translationKey = 'HasCreditNoteFromSupplier';
				}
				else
				{
					if (!$filter || $filter == "fk_invoice_supplier_source IS NULL OR (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS PAID)%')") $translationKey = 'HasAbsoluteDiscountFromSupplier';
					else $translationKey = 'HasCreditNoteFromSupplier';
				}
			} else {
				if (!empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS))
				{
					if (!$filter || $filter == "fk_facture_source IS NULL") $translationKey = 'CompanyHasAbsoluteDiscount';   
					else $translationKey = 'CompanyHasCreditNote';
				}
				else
				{
					if (!$filter || $filter == "fk_facture_source IS NULL OR (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS RECEIVED)%')") $translationKey = 'CompanyHasAbsoluteDiscount';
					else $translationKey = 'CompanyHasCreditNote';
				}
			}
			print $langs->trans($translationKey, price($amount, 0, $langs, 0, 0, -1, $conf->currency));
			if (empty($hidelist)) print ': ';
			print '</div>';
			if (empty($hidelist))
			{
				print '<div class="inline-block" style="padding-right: 10px">';
				$newfilter = 'discount_type='.intval($discount_type);
				if (!empty($discount_type)) {
					$newfilter .= ' AND fk_invoice_supplier IS NULL AND fk_invoice_supplier_line IS NULL';   
				} else {
					$newfilter .= ' AND fk_facture IS NULL AND fk_facture_line IS NULL';   
				}
				if ($filter) $newfilter .= ' AND ('.$filter.')';
				$nbqualifiedlines = $this->select_remises($selected, $htmlname, $newfilter, $socid, $maxvalue);
				if ($nbqualifiedlines > 0)
				{
					print ' &nbsp; <input type="submit" class="button" value="'.dol_escape_htmltag($langs->trans("UseLine")).'"';
					if (!empty($discount_type) && $filter && $filter != "fk_invoice_supplier_source IS NULL OR (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS PAID)%')")
						print ' title="'.$langs->trans("UseCreditNoteInInvoicePayment").'"';
					if (empty($discount_type) && $filter && $filter != "fk_facture_source IS NULL OR (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS RECEIVED)%')")
						print ' title="'.$langs->trans("UseCreditNoteInInvoicePayment").'"';

					print '>';
				}
				print '</div>';
			}
			if ($more)
			{
				print '<div class="inline-block">';
				print $more;
				print '</div>';
			}
			print '</form>';
		}
		else
		{
			if ($selected)
			{
				print $selected;
			}
			else
			{
				print "0";
			}
		}
	}


      
   
    public function form_contacts($page, $societe, $selected = '', $htmlname = 'contactid')
    {
          
		global $langs, $conf;

		if ($htmlname != "none")
		{
			print '<form method="post" action="'.$page.'">';
			print '<input type="hidden" name="action" value="set_contact">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
			print '<tr><td>';
			$num = $this->select_contacts($societe->id, $selected, $htmlname);
			if ($num == 0)
			{
				$addcontact = (!empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("AddContact") : $langs->trans("AddContactAddress"));
				print '<a href="'.DOL_URL_ROOT.'/contact/card.php?socid='.$societe->id.'&amp;action=create&amp;backtoreferer=1">'.$addcontact.'</a>';
			}
			print '</td>';
			print '<td class="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
			print '</tr></table></form>';
		}
		else
		{
			if ($selected)
			{
				require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
				$contact = new Contact($this->db);
				$contact->fetch($selected);
				print $contact->getFullName($langs);
			} else {
				print "&nbsp;";
			}
		}
	}

      
	
    public function form_thirdparty($page, $selected = '', $htmlname = 'socid', $filter = '', $showempty = 0, $showtype = 0, $forcecombo = 0, $events = array(), $nooutput = 0)
	{
          
		global $langs;

		$out = '';
		if ($htmlname != "none")
		{
			$out .= '<form method="post" action="'.$page.'">';
			$out .= '<input type="hidden" name="action" value="set_thirdparty">';
			$out .= '<input type="hidden" name="token" value="'.newToken().'">';
			$out .= $this->select_company($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events);
			$out .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			$out .= '</form>';
		}
		else
		{
			if ($selected)
			{
				require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
				$soc = new Societe($this->db);
				$soc->fetch($selected);
				$out .= $soc->getNomUrl($langs);
			}
			else
			{
				$out .= "&nbsp;";
			}
		}

		if ($nooutput) return $out;
		else print $out;
	}

      
	
    public function select_currency($selected = '', $htmlname = 'currency_id')
	{
          
		print $this->selectCurrency($selected, $htmlname);
	}

	
    public function selectCurrency($selected = '', $htmlname = 'currency_id', $mode = 0)
	{
		global $conf, $langs, $user;

		$langs->loadCacheCurrencies('');

		$out = '';

		if ($selected == 'euro' || $selected == 'euros') $selected = 'EUR';   

		$out .= '<select class="flat maxwidth200onsmartphone minwidth300" name="'.$htmlname.'" id="'.$htmlname.'">';
		foreach ($langs->cache_currencies as $code_iso => $currency)
		{
			if ($selected && $selected == $code_iso)
			{
				$out .= '<option value="'.$code_iso.'" selected>';
			}
			else
			{
				$out .= '<option value="'.$code_iso.'">';
			}
			$out .= $currency['label'];
			if ($mode == 1)
			{
			    $out .= ' ('.$code_iso.')';
			}
			else
			{
                $out .= ' ('.$langs->getCurrencySymbol($code_iso).')';
			}
			$out .= '</option>';
		}
		$out .= '</select>';
		if ($user->admin) $out .= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);

		  
		include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
		$out .= ajax_combobox($htmlname);

		return $out;
	}

	
    public function selectMultiCurrency($selected = '', $htmlname = 'multicurrency_code', $useempty = 0)
	{
		global $db, $conf, $langs, $user;

		$langs->loadCacheCurrencies('');   

		$TCurrency = array();

		$sql = 'SELECT code FROM '.MAIN_DB_PREFIX.'multicurrency';
		$sql .= " WHERE entity IN ('".getEntity('mutlicurrency')."')";
		$resql = $db->query($sql);
		if ($resql)
		{
			while ($obj = $db->fetch_object($resql)) $TCurrency[$obj->code] = $obj->code;
		}

		$out = '';
		$out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
		if ($useempty) $out .= '<option value="">&nbsp;</option>';
		  
		if (!in_array($conf->currency, $TCurrency))
		{
			$TCurrency[$conf->currency] = $conf->currency;
		}
		if (count($TCurrency) > 0)
		{
			foreach ($langs->cache_currencies as $code_iso => $currency)
			{
				if (isset($TCurrency[$code_iso]))
				{
					if (!empty($selected) && $selected == $code_iso) $out .= '<option value="'.$code_iso.'" selected="selected">';
					else $out .= '<option value="'.$code_iso.'">';

					$out .= $currency['label'];
					$out .= ' ('.$langs->getCurrencySymbol($code_iso).')';
					$out .= '</option>';
				}
			}
		}

		$out .= '</select>';
		  
		include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
		$out .= ajax_combobox($htmlname);

		return $out;
	}

      
	
    public function load_cache_vatrates($country_code)
	{
          
		global $langs;

		$num = count($this->cache_vatrates);
		if ($num > 0) return $num;   

		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT DISTINCT t.rowid, t.code, t.taux, t.localtax1, t.localtax1_type, t.localtax2, t.localtax2_type, t.recuperableonly";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_tva as t, ".MAIN_DB_PREFIX."c_country as c";
		$sql .= " WHERE t.fk_pays = c.rowid";
		$sql .= " AND t.active > 0";
		$sql .= " AND c.code IN (".$country_code.")";
		$sql .= " ORDER BY t.code ASC, t.taux ASC, t.recuperableonly ASC";

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			if ($num)
			{
				for ($i = 0; $i < $num; $i++)
				{
					$obj = $this->db->fetch_object($resql);
					$this->cache_vatrates[$i]['rowid']	= $obj->rowid;
					$this->cache_vatrates[$i]['code'] = $obj->code;
					$this->cache_vatrates[$i]['txtva']	= $obj->taux;
					$this->cache_vatrates[$i]['nprtva'] = $obj->recuperableonly;
					$this->cache_vatrates[$i]['localtax1']	    = $obj->localtax1;
					$this->cache_vatrates[$i]['localtax1_type']	= $obj->localtax1_type;
					$this->cache_vatrates[$i]['localtax2']	    = $obj->localtax2;
					$this->cache_vatrates[$i]['localtax2_type']	= $obj->localtax1_type;

					$this->cache_vatrates[$i]['label'] = $obj->taux.'%'.($obj->code ? ' ('.$obj->code.')' : '');   
					$this->cache_vatrates[$i]['labelallrates'] = $obj->taux.'/'.($obj->localtax1 ? $obj->localtax1 : '0').'/'.($obj->localtax2 ? $obj->localtax2 : '0').($obj->code ? ' ('.$obj->code.')' : '');   
					$positiverates = '';
					if ($obj->taux) $positiverates .= ($positiverates ? '/' : '').$obj->taux;
					if ($obj->localtax1) $positiverates .= ($positiverates ? '/' : '').$obj->localtax1;
					if ($obj->localtax2) $positiverates .= ($positiverates ? '/' : '').$obj->localtax2;
					if (empty($positiverates)) $positiverates = '0';
					$this->cache_vatrates[$i]['labelpositiverates'] = $positiverates.($obj->code ? ' ('.$obj->code.')' : '');   
				}

				return $num;
			}
			else
			{
				$this->error = '<font class="error">'.$langs->trans("ErrorNoVATRateDefinedForSellerCountry", $country_code).'</font>';
				return -1;
			}
		}
		else
		{
			$this->error = '<font class="error">'.$this->db->error().'</font>';
			return -2;
		}
	}

      
	
    public function load_tva($htmlname = 'tauxtva', $selectedrate = '', $societe_vendeuse = '', $societe_acheteuse = '', $idprod = 0, $info_bits = 0, $type = '', $options_only = false, $mode = 0)
	{
          
		global $langs, $conf, $mysoc;

		$langs->load('errors');

		$return = '';

		  
		$defaultnpr = ($info_bits & 0x01);
		$defaultnpr = (preg_match('/\*/', $selectedrate) ? 1 : $defaultnpr);
		$defaulttx = str_replace('*', '', $selectedrate);
		$defaultcode = '';
		if (preg_match('/\((.*)\)/', $defaulttx, $reg))
		{
			$defaultcode = $reg[1];
			$defaulttx = preg_replace('/\s*\(.*\)/', '', $defaulttx);
		}
		  

		  
		if (is_object($societe_vendeuse) && !$societe_vendeuse->country_code)
		{
			if ($societe_vendeuse->id == $mysoc->id)
			{
				$return .= '<font class="error">'.$langs->trans("ErrorYourCountryIsNotDefined").'</font>';
			}
			else
			{
				$return .= '<font class="error">'.$langs->trans("ErrorSupplierCountryIsNotDefined").'</font>';
			}
			return $return;
		}

		  
		  
		  

		  
		  
		if (is_object($societe_vendeuse))
		{
			$code_country = "'".$societe_vendeuse->country_code."'";
		}
		else
		{
			$code_country = "'".$mysoc->country_code."'";   
		}
		if (!empty($conf->global->SERVICE_ARE_ECOMMERCE_200238EC))      
		{
			require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
			if (!isInEEC($societe_vendeuse) && (!is_object($societe_acheteuse) || (isInEEC($societe_acheteuse) && !$societe_acheteuse->isACompany())))
			{
				  
				if (is_numeric($type))
				{
					if ($type == 1)   
					{
						$code_country .= ",'".$societe_acheteuse->country_code."'";
					}
				}
				elseif (!$idprod)    
				{
					$code_country .= ",'".$societe_acheteuse->country_code."'";
				}
				else
				{
					$prodstatic = new Product($this->db);
					$prodstatic->fetch($idprod);
					if ($prodstatic->type == Product::TYPE_SERVICE)     
					{
						$code_country .= ",'".$societe_acheteuse->country_code."'";
					}
				}
			}
		}

		  
		$num = $this->load_cache_vatrates($code_country);   

		if ($num > 0)
		{
			  
			if ($defaulttx < 0 || dol_strlen($defaulttx) == 0)
			{
				$tmpthirdparty = new Societe($this->db);
				$defaulttx = get_default_tva($societe_vendeuse, (is_object($societe_acheteuse) ? $societe_acheteuse : $tmpthirdparty), $idprod);
				$defaultnpr = get_default_npr($societe_vendeuse, (is_object($societe_acheteuse) ? $societe_acheteuse : $tmpthirdparty), $idprod);
		        if (preg_match('/\((.*)\)/', $defaulttx, $reg)) {
			        $defaultcode = $reg[1];
			        $defaulttx = preg_replace('/\s*\(.*\)/', '', $defaulttx);
		        }
				if (empty($defaulttx)) $defaultnpr = 0;
			}

			  
			  
			if ($defaulttx < 0 || dol_strlen($defaulttx) == 0)
			{
				if (empty($conf->global->MAIN_VAT_DEFAULT_IF_AUTODETECT_FAILS)) $defaulttx = $this->cache_vatrates[$num - 1]['txtva'];
				else $defaulttx = ($conf->global->MAIN_VAT_DEFAULT_IF_AUTODETECT_FAILS == 'none' ? '' : $conf->global->MAIN_VAT_DEFAULT_IF_AUTODETECT_FAILS);
			}

			  
			$disabled = false; $title = '';
			if (is_object($societe_vendeuse) && $societe_vendeuse->id == $mysoc->id && $societe_vendeuse->tva_assuj == "0")
			{
				  
				if (empty($conf->global->OVERRIDE_VAT_FOR_EXPENSE_REPORT))
				{
					$title = ' title="'.$langs->trans('VATIsNotUsed').'"';
					$disabled = true;
				}
			}

			if (!$options_only) $return .= '<select class="flat minwidth75imp" id="'.$htmlname.'" name="'.$htmlname.'"'.($disabled ? ' disabled' : '').$title.'>';

			$selectedfound = false;
			foreach ($this->cache_vatrates as $rate)
			{
				  
				if ($disabled && $rate['txtva'] != 0) continue;

				  
				$key = $rate['txtva'];
				$key .= $rate['nprtva'] ? '*' : '';
				if ($mode > 0 && $rate['code']) $key .= ' ('.$rate['code'].')';
				if ($mode < 0) $key = $rate['rowid'];

				$return .= '<option value="'.$key.'"';
				if (!$selectedfound)
				{
					if ($defaultcode)   
					{
						if ($defaultcode == $rate['code'])
						{
							$return .= ' selected';
							$selectedfound = true;
						}
					}
					elseif ($rate['txtva'] == $defaulttx && $rate['nprtva'] == $defaultnpr)
			   		{
			   			$return .= ' selected';
			   			$selectedfound = true;
					}
				}
				$return .= '>';
				  
				if ($mysoc->country_code == 'IN' || !empty($conf->global->MAIN_VAT_LABEL_IS_POSITIVE_RATES))
				{
					$return .= $rate['labelpositiverates'];
				}
				else
				{
					$return .= vatrate($rate['label']);
				}
				  
				$return .= (empty($rate['code']) && $rate['nprtva']) ? ' *' : '';   

				$return .= '</option>';
			}

			if (!$options_only) $return .= '</select>';
		}
		else
		{
			$return .= $this->error;
		}

		$this->num = $num;
		return $return;
	}


      
    
    public function select_date($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $nooutput = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '')
    {
          
        $retstring = $this->selectDate($set_time, $prefix, $h, $m, $empty, $form_name, $d, $addnowlink, $disabled, $fullday, $addplusone, $adddateof);
        if (!empty($nooutput)) {
            return $retstring;
        }
        print $retstring;
        return;
    }

    
    public function selectDate($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '', $openinghours = '', $stepminutes = 1, $labeladddateof = '')
	{
		global $conf, $langs;

		$retstring = '';

		if ($prefix == '') $prefix = 're';
		if ($h == '') $h = 0;
		if ($m == '') $m = 0;
		$emptydate = 0;
		$emptyhours = 0;
        if ($stepminutes <= 0 || $stepminutes > 30) $stepminutes = 1;
		if ($empty == 1) { $emptydate = 1; $emptyhours = 1; }
		if ($empty == 2) { $emptydate = 0; $emptyhours = 1; }
		$orig_set_time = $set_time;

		if ($set_time === '' && $emptydate == 0)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			$set_time = dol_now('tzuser') - (getServerTimeZoneInt('now') * 3600);   
		}

		  
		if (preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\s?([0-9]+)?:?([0-9]+)?/', $set_time, $reg))	  
		{
			  
			$syear	= (!empty($reg[1]) ? $reg[1] : '');
			$smonth = (!empty($reg[2]) ? $reg[2] : '');
			$sday	= (!empty($reg[3]) ? $reg[3] : '');
			$shour	= (!empty($reg[4]) ? $reg[4] : '');
			$smin	= (!empty($reg[5]) ? $reg[5] : '');
		}
		elseif (strval($set_time) != '' && $set_time != -1)
		{
			  
			$syear = dol_print_date($set_time, "%Y");
			$smonth = dol_print_date($set_time, "%m");
			$sday = dol_print_date($set_time, "%d");
			if ($orig_set_time != '')
			{
				$shour = dol_print_date($set_time, "%H");
				$smin = dol_print_date($set_time, "%M");
				$ssec = dol_print_date($set_time, "%S");
			}
			else
			{
				$shour = '';
				$smin = '';
				$ssec = '';
			}
		}
		else
		{
			  
			$syear = '';
			$smonth = '';
			$sday = '';
			$shour = !isset($conf->global->MAIN_DEFAULT_DATE_HOUR) ? ($h == -1 ? '23' : '') : $conf->global->MAIN_DEFAULT_DATE_HOUR;
			$smin = !isset($conf->global->MAIN_DEFAULT_DATE_MIN) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_MIN;
			$ssec = !isset($conf->global->MAIN_DEFAULT_DATE_SEC) ? ($h == -1 ? '59' : '') : $conf->global->MAIN_DEFAULT_DATE_SEC;
		}
		if ($h == 3) $shour = '';
		if ($m == 3) $smin = '';

		  
		$usecalendar = 'combo';
		if (!empty($conf->use_javascript_ajax) && (empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR != "none")) {
			$usecalendar = ((empty($conf->global->MAIN_POPUP_CALENDAR) || $conf->global->MAIN_POPUP_CALENDAR == 'eldy') ? 'jquery' : $conf->global->MAIN_POPUP_CALENDAR);
		}

		if ($d)
		{
			  
			if ($usecalendar != 'combo')
			{
				$formated_date = '';
				  
				if (strval($set_time) != '' && $set_time != -1)
				{
					  
					$formated_date = dol_print_date($set_time, $langs->trans("FormatDateShortInput"));   
				}

				  
				if ($usecalendar == "eldy")
				{
					  
					$retstring .= '<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth75" maxlength="11" value="'.$formated_date.'"';
					$retstring .= ($disabled ? ' disabled' : '');
					$retstring .= ' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';   
					$retstring .= '>';

					  
					if (!$disabled)
					{
						$retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons"';
						$base = DOL_URL_ROOT.'/core/';
						$retstring .= ' onClick="showDP(\''.$base.'\',\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\',\''.$langs->defaultlang.'\');"';
						$retstring .= '>'.img_object($langs->trans("SelectDate"), 'calendarday', 'class="datecallink"').'</button>';
					}
					else $retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';

					$retstring .= '<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
					$retstring .= '<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
					$retstring .= '<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
				}
				elseif ($usecalendar == 'jquery')
				{
					if (!$disabled)
					{
						  
						$retstring .= "<script type='text/javascript'>";
						$retstring .= "$(function(){ $('#".$prefix."').datepicker({
							dateFormat: '".$langs->trans("FormatDateShortJQueryInput")."',
							autoclose: true,
							todayHighlight: true,";
						if (!empty($conf->dol_use_jmobile))
						{
							$retstring .= "
								beforeShow: function (input, datePicker) {
									input.disabled = true;
								},
								onClose: function (dateText, datePicker) {
									this.disabled = false;
								},
								";
						}
						  
						if (empty($conf->global->MAIN_POPUP_CALENDAR_ON_FOCUS))
						{
							$retstring .= "
								showOn: 'button',
								buttonImage: '".DOL_URL_ROOT."/theme/".$conf->theme."/img/object_calendarday.png',
								buttonImageOnly: true";
						}
						$retstring .= "
							}) });";
						$retstring .= "</script>";
					}

					  
					$retstring .= '<div class="nowrap inline-block">';
					$retstring .= '<input id="'.$prefix.'" name="'.$prefix.'" type="text" class="maxwidth75" maxlength="11" value="'.$formated_date.'"';
					$retstring .= ($disabled ? ' disabled' : '');
					$retstring .= ' onChange="dpChangeDay(\''.$prefix.'\',\''.$langs->trans("FormatDateShortJavaInput").'\'); "';   
					$retstring .= '>';

					  
					if (!$disabled)
					{
						
					}
					else
					{
						$retstring .= '<button id="'.$prefix.'Button" type="button" class="dpInvisibleButtons">'.img_object($langs->trans("Disabled"), 'calendarday', 'class="datecallink"').'</button>';
					}

					$retstring .= '</div>';
					$retstring .= '<input type="hidden" id="'.$prefix.'day"   name="'.$prefix.'day"   value="'.$sday.'">'."\n";
					$retstring .= '<input type="hidden" id="'.$prefix.'month" name="'.$prefix.'month" value="'.$smonth.'">'."\n";
					$retstring .= '<input type="hidden" id="'.$prefix.'year"  name="'.$prefix.'year"  value="'.$syear.'">'."\n";
				}
				else
				{
					$retstring .= "Bad value of MAIN_POPUP_CALENDAR";
				}
			}
			  
			else
			{
				  
				  
				$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50imp" id="'.$prefix.'day" name="'.$prefix.'day">';

				if ($emptydate || $set_time == -1)
				{
					$retstring .= '<option value="0" selected>&nbsp;</option>';
				}

				for ($day = 1; $day <= 31; $day++)
				{
					$retstring .= '<option value="'.$day.'"'.($day == $sday ? ' selected' : '').'>'.$day.'</option>';
				}

				$retstring .= "</select>";

				$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'month" name="'.$prefix.'month">';
				if ($emptydate || $set_time == -1)
				{
					$retstring .= '<option value="0" selected>&nbsp;</option>';
				}

				  
				for ($month = 1; $month <= 12; $month++)
				{
					$retstring .= '<option value="'.$month.'"'.($month == $smonth ? ' selected' : '').'>';
					$retstring .= dol_print_date(mktime(12, 0, 0, $month, 1, 2000), "%b");
					$retstring .= "</option>";
				}
				$retstring .= "</select>";

				  
				if ($emptydate || $set_time == -1)
				{
					$retstring .= '<input'.($disabled ? ' disabled' : '').' placeholder="'.dol_escape_htmltag($langs->trans("Year")).'" class="flat maxwidth50imp valignmiddle" type="number" min="0" max="3000" maxlength="4" id="'.$prefix.'year" name="'.$prefix.'year" value="'.$syear.'">';
				}
				else
				{
					$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth75imp" id="'.$prefix.'year" name="'.$prefix.'year">';

					for ($year = $syear - 10; $year < $syear + 10; $year++)
					{
						$retstring .= '<option value="'.$year.'"'.($year == $syear ? ' selected' : '').'>'.$year.'</option>';
					}
					$retstring .= "</select>\n";
				}
				  
			}
		}

		if ($d && $h) $retstring .= ($h == 2 ? '<br>' : ' ');

		if ($h)
		{
			$hourstart = 0;
			$hourend = 24;
			if ($openinghours != '') {
				$openinghours = explode(',', $openinghours);
				$hourstart = $openinghours[0];
				$hourend = $openinghours[1];
				if ($hourend < $hourstart) $hourend = $hourstart;
			}
			  
			$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50 '.($fullday ? $fullday.'hour' : '').'" id="'.$prefix.'hour" name="'.$prefix.'hour">';
			if ($emptyhours) $retstring .= '<option value="-1">&nbsp;</option>';
			for ($hour = $hourstart; $hour < $hourend; $hour++)
			{
				if (strlen($hour) < 2) $hour = "0".$hour;
				$retstring .= '<option value="'.$hour.'"'.(($hour == $shour) ? ' selected' : '').'>'.$hour.(empty($conf->dol_optimize_smallscreen) ? '' : 'H').'</option>';
			}
			$retstring .= '</select>';
			if ($m && empty($conf->dol_optimize_smallscreen)) $retstring .= ":";
		}

		if ($m)
		{
			  
			$retstring .= '<select'.($disabled ? ' disabled' : '').' class="flat valignmiddle maxwidth50 '.($fullday ? $fullday.'min' : '').'" id="'.$prefix.'min" name="'.$prefix.'min">';
			if ($emptyhours) $retstring .= '<option value="-1">&nbsp;</option>';
			for ($min = 0; $min < 60; $min += $stepminutes)
			{
				if (strlen($min) < 2) $min = "0".$min;
				$retstring .= '<option value="'.$min.'"'.(($min == $smin) ? ' selected' : '').'>'.$min.(empty($conf->dol_optimize_smallscreen) ? '' : '').'</option>';
			}
			$retstring .= '</select>';

			$retstring .= '<input type="hidden" name="'.$prefix.'sec" value="'.$ssec.'">';
		}

		  
		if ($conf->use_javascript_ajax && $addnowlink)
		{
			  
			$reset_scripts = "";
            if ($addnowlink == 2)   
            {
                  
                $reset_scripts .= "Number.prototype.pad = function(size) {
                        var s = String(this);
                        while (s.length < (size || 2)) {s = '0' + s;}
                        return s;
                    };
                    var d = new Date();";
            }

			  
            if ($addnowlink == 1)   
            {
                $reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day', 'tzuser').'\');';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d', 'tzuser').'\');';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m', 'tzuser').'\');';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y', 'tzuser').'\');';
            }
            elseif ($addnowlink == 2)
            {
                $reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(d.toLocaleDateString(\''.str_replace('_', '-', $langs->defaultlang).'\'));';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(d.getDate().pad());';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(parseInt(d.getMonth().pad()) + 1);';
                $reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(d.getFullYear());';
            }
			
			  
			if ($h)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				  
                if ($addnowlink == 1)
                {
                    $reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H', 'tzuser').'\');';
                }
                elseif ($addnowlink == 2)
                {
                    $reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(d.getHours().pad());';
                }

				if ($fullday) $reset_scripts .= ' } ';
			}
			  
			if ($m)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				  
                if ($addnowlink == 1)
                {
                    $reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M', 'tzuser').'\');';
                }
                elseif ($addnowlink == 2)
                {
                    $reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(d.getMinutes().pad());';
                }
				if ($fullday) $reset_scripts .= ' } ';
			}
			  
			if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
			{
				$retstring .= ' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonNow" type="button" name="_useless" value="now" onClick="'.$reset_scripts.'">';
				$retstring .= $langs->trans("Now");
				$retstring .= '</button> ';
			}
		}

		  
		if ($conf->use_javascript_ajax && $addplusone)
		{
			  
			$reset_scripts = "";

			  
			$reset_scripts .= 'jQuery(\'#'.$prefix.'\').val(\''.dol_print_date(dol_now(), 'day').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'day\').val(\''.dol_print_date(dol_now(), '%d').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'month\').val(\''.dol_print_date(dol_now(), '%m').'\');';
			$reset_scripts .= 'jQuery(\'#'.$prefix.'year\').val(\''.dol_print_date(dol_now(), '%Y').'\');';
			  
			if ($h)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				$reset_scripts .= 'jQuery(\'#'.$prefix.'hour\').val(\''.dol_print_date(dol_now(), '%H').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			  
			if ($m)
			{
				if ($fullday) $reset_scripts .= " if (jQuery('#fullday:checked').val() == null) {";
				$reset_scripts .= 'jQuery(\'#'.$prefix.'min\').val(\''.dol_print_date(dol_now(), '%M').'\');';
				if ($fullday) $reset_scripts .= ' } ';
			}
			  
			if ($reset_scripts && empty($conf->dol_optimize_smallscreen))
			{
				$retstring .= ' <button class="dpInvisibleButtons datenowlink" id="'.$prefix.'ButtonPlusOne" type="button" name="_useless2" value="plusone" onClick="'.$reset_scripts.'">';
				$retstring .= $langs->trans("DateStartPlusOne");
				$retstring .= '</button> ';
			}
		}

		  
		if ($conf->use_javascript_ajax && $adddateof)
		{
			$tmparray = dol_getdate($adddateof);
			if (empty($labeladddateof)) $labeladddateof = $langs->trans("DateInvoice");
			$retstring .= ' - <button class="dpInvisibleButtons datenowlink" id="dateofinvoice" type="button" name="_dateofinvoice" value="now" onclick="jQuery(\'#re\').val(\''.dol_print_date($adddateof, 'day').'\');jQuery(\'#reday\').val(\''.$tmparray['mday'].'\');jQuery(\'#remonth\').val(\''.$tmparray['mon'].'\');jQuery(\'#reyear\').val(\''.$tmparray['year'].'\');">'.$labeladddateof.'</a>';
		}

		return $retstring;
	}

      
    
    public function select_duration($prefix, $iSecond = '', $disabled = 0, $typehour = 'select', $minunderhours = 0, $nooutput = 0)
	{
          
		global $langs;

		$retstring = '';

		$hourSelected = 0; $minSelected = 0;

		  
		if ($iSecond != '')
		{
			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

			$hourSelected = convertSecondToTime($iSecond, 'allhour');
			$minSelected = convertSecondToTime($iSecond, 'min');
		}

		if ($typehour == 'select')
		{
			$retstring .= '<select class="flat" id="select_'.$prefix.'hour" name="'.$prefix.'hour"'.($disabled ? ' disabled' : '').'>';
			for ($hour = 0; $hour < 25; $hour++)	  
			{
				$retstring .= '<option value="'.$hour.'"';
				if ($hourSelected == $hour)
				{
					$retstring .= " selected";
				}
				$retstring .= ">".$hour."</option>";
			}
			$retstring .= "</select>";
		}
		elseif ($typehour == 'text' || $typehour == 'textselect')
		{
			$retstring .= '<input placeholder="'.$langs->trans('HourShort').'" type="number" min="0" size="1" name="'.$prefix.'hour"'.($disabled ? ' disabled' : '').' class="flat maxwidth50 inputhour" value="'.(($hourSelected != '') ? ((int) $hourSelected) : '').'">';
		}
		else return 'BadValueForParameterTypeHour';

		if ($typehour != 'text') $retstring .= ' '.$langs->trans('HourShort');
		else $retstring .= '<span class="hideonsmartphone">:</span>';

		  
		if ($minunderhours) $retstring .= '<br>';
		else $retstring .= '<span class="hideonsmartphone">&nbsp;</span>';

		if ($typehour == 'select' || $typehour == 'textselect')
		{
			$retstring .= '<select class="flat" id="select_'.$prefix.'min" name="'.$prefix.'min"'.($disabled ? ' disabled' : '').'>';
			for ($min = 0; $min <= 55; $min = $min + 5)
			{
				$retstring .= '<option value="'.$min.'"';
				if ($minSelected == $min) $retstring .= ' selected';
				$retstring .= '>'.$min.'</option>';
			}
			$retstring .= "</select>";
		}
		elseif ($typehour == 'text')
		{
			$retstring .= '<input placeholder="'.$langs->trans('MinuteShort').'" type="number" min="0" size="1" name="'.$prefix.'min"'.($disabled ? ' disabled' : '').' class="flat maxwidth50 inputminute" value="'.(($minSelected != '') ? ((int) $minSelected) : '').'">';
		}

		if ($typehour != 'text') $retstring .= ' '.$langs->trans('MinuteShort');

		  

		if (!empty($nooutput)) return $retstring;

		print $retstring;
		return;
	}


	
	public function selectForForms($objectdesc, $htmlname, $preselectedvalue, $showempty = '', $searchkey = '', $placeholder = '', $morecss = '', $moreparams = '', $forcecombo = 0, $disabled = 0, $selected_input_value = '')
	{
		global $conf, $user;

		$objecttmp = null;

		$InfoFieldList = explode(":", $objectdesc);
		$classname = $InfoFieldList[0];
		$classpath = $InfoFieldList[1];
		$addcreatebuttonornot = empty($InfoFieldList[2]) ? 0 : $InfoFieldList[2];
		$filter = empty($InfoFieldList[3]) ? '' : $InfoFieldList[3];

		if (!empty($classpath))
		{
			dol_include_once($classpath);
			if ($classname && class_exists($classname))
			{
				$objecttmp = new $classname($this->db);
				  
				$sharedentities = getEntity(strtolower($classname));
				$objecttmp->filter = str_replace(
					array('__ENTITY__', '__SHARED_ENTITIES__', '__USER_ID__'),
					array($conf->entity, $sharedentities, $user->id),
					$filter);
			}
		}
		if (!is_object($objecttmp))
		{
			dol_syslog('Error bad setup of type for field '.$InfoFieldList, LOG_WARNING);
			return 'Error bad setup of type for field '.join(',', $InfoFieldList);
		}

		  
		$prefixforautocompletemode = $objecttmp->element;
		if ($prefixforautocompletemode == 'societe') $prefixforautocompletemode = 'company';
		if ($prefixforautocompletemode == 'product') $prefixforautocompletemode='produit';
		$confkeyforautocompletemode = strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT';   

		dol_syslog(get_class($this)."::selectForForms object->filter=".$objecttmp->filter, LOG_DEBUG);
		$out = '';
		if (!empty($conf->use_javascript_ajax) && !empty($conf->global->$confkeyforautocompletemode) && !$forcecombo)
		{
		      
		    $placeholder = '';
		    if ($preselectedvalue && empty($selected_input_value))
		    {
		        $objecttmp->fetch($preselectedvalue);
		        $selected_input_value = ($prefixforautocompletemode == 'company' ? $objecttmp->name : $objecttmp->ref);
		          
		    }

		    $objectdesc = $classname.':'.$classpath.':'.$addcreatebuttonornot.':'.$filter;
			$urlforajaxcall = DOL_URL_ROOT.'/core/ajax/selectobject.php';

			  
			$urloption = 'htmlname='.$htmlname.'&outjson=1&objectdesc='.$objectdesc.'&filter='.urlencode($objecttmp->filter).($moreparams ? $moreparams : '');
			  
			$out .= ajax_autocompleter($preselectedvalue, $htmlname, $urlforajaxcall, $urloption, $conf->global->$confkeyforautocompletemode, 0, array());
			$out .= '<style type="text/css">.ui-autocomplete { z-index: 250; }</style>';
			if ($placeholder) $placeholder = ' placeholder="'.$placeholder.'"';
			$out .= '<input type="text" class="'.$morecss.'"'.($disabled ? ' disabled="disabled"' : '').' name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' />';
		}
		else
		{
			  
			$out .= $this->selectForFormsList($objecttmp, $htmlname, $preselectedvalue, $showempty, $searchkey, $placeholder, $morecss, $moreparams, $forcecombo, 0, $disabled);
		}

		return $out;
	}

	
	protected static function forgeCriteriaCallback($matches)
	{
		global $db;

		  
		if (empty($matches[1])) return '';
		$tmp = explode(':', $matches[1]);
		if (count($tmp) < 3) return '';

		$tmpescaped = $tmp[2];
		$regbis = array();
		if (preg_match('/^\'(.*)\'$/', $tmpescaped, $regbis))
		{
			$tmpescaped = "'".$db->escape($regbis[1])."'";
		}
		else
		{
			$tmpescaped = $db->escape($tmpescaped);
		}
		return $db->escape($tmp[0]).' '.strtoupper($db->escape($tmp[1]))." ".$tmpescaped;
	}

	
    public function selectForFormsList($objecttmp, $htmlname, $preselectedvalue, $showempty = '', $searchkey = '', $placeholder = '', $morecss = '', $moreparams = '', $forcecombo = 0, $outputmode = 0, $disabled = 0)
	{
		global $conf, $langs, $user;

		  

		$prefixforautocompletemode = $objecttmp->element;
		if ($prefixforautocompletemode == 'societe') $prefixforautocompletemode = 'company';
		$confkeyforautocompletemode = strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT';   

		if (!empty($objecttmp->fields))	  
		{
			$tmpfieldstoshow = '';
			foreach ($objecttmp->fields as $key => $val)
			{
				if ($val['showoncombobox']) $tmpfieldstoshow .= ($tmpfieldstoshow ? ',' : '').'t.'.$key;
			}
			if ($tmpfieldstoshow) $fieldstoshow = $tmpfieldstoshow;
		}
        else
        {
			  
			$objecttmp->fields['ref'] = array('type'=>'varchar(30)', 'label'=>'Ref', 'showoncombobox'=>1);
        }

		if (empty($fieldstoshow))
		{
			if (isset($objecttmp->fields['ref'])) {
				$fieldstoshow = 't.ref';
			}
			else
			{
				$langs->load("errors");
				$this->error = $langs->trans("ErrorNoFieldWithAttributeShowoncombobox");
				return $langs->trans('ErrorNoFieldWithAttributeShowoncombobox');
			}
		}

		$out = '';
		$outarray = array();

		$num = 0;

		  
		$sql = "SELECT t.rowid, ".$fieldstoshow." FROM ".MAIN_DB_PREFIX.$objecttmp->table_element." as t";
		if ($objecttmp->ismultientitymanaged == 2)
			if (!$user->rights->societe->client->voir && !$user->socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		$sql .= " WHERE 1=1";
		if (!empty($objecttmp->ismultientitymanaged)) $sql .= " AND t.entity IN (".getEntity($objecttmp->table_element).")";
		if ($objecttmp->ismultientitymanaged == 1 && !empty($user->socid)) {
			if ($objecttmp->element == 'societe') $sql .= " AND t.rowid = ".$user->socid;
			else $sql .= " AND t.fk_soc = ".$user->socid;
		}
		if ($searchkey != '') $sql .= natural_search(explode(',', $fieldstoshow), $searchkey);
		if ($objecttmp->ismultientitymanaged == 2) {
			if (!$user->rights->societe->client->voir && !$user->socid) $sql .= " AND t.rowid = sc.fk_soc AND sc.fk_user = ".$user->id;
		}
		if ($objecttmp->filter) {	   
			
			$regexstring = '\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
			$sql .= " AND (".preg_replace_callback('/'.$regexstring.'/', 'Form::forgeCriteriaCallback', $objecttmp->filter).")";
		}
		$sql .= $this->db->order($fieldstoshow, "ASC");
		  
		  

		  
		$resql = $this->db->query($sql);
		if ($resql)
		{
			if (!$forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, null, $conf->global->$confkeyforautocompletemode);
			}

			  
			$out .= '<select id="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($disabled ? ' disabled="disabled"' : '').($moreparams ? ' '.$moreparams : '').' name="'.$htmlname.'">'."\n";

			  
			$textifempty = '&nbsp;';

			  
			if (!empty($conf->global->$confkeyforautocompletemode))
			{
				if ($showempty && !is_numeric($showempty)) $textifempty = $langs->trans($showempty);
				else $textifempty .= $langs->trans("All");
			}
			if ($showempty) $out .= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$label = '';
					$tmparray = explode(',', $fieldstoshow);
					foreach ($tmparray as $key => $val)
					{
						$val = preg_replace('/t\./', '', $val);
						$label .= (($label && $obj->$val) ? ' - ' : '').$obj->$val;
					}
					if (empty($outputmode))
					{
						if ($preselectedvalue > 0 && $preselectedvalue == $obj->rowid)
						{
							$out .= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
						}
						else
						{
							$out .= '<option value="'.$obj->rowid.'">'.$label.'</option>';
						}
					}
					else
					{
						array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));
					}

					$i++;
					if (($i % 10) == 0) $out .= "\n";
				}
			}

			$out .= '</select>'."\n";
		}
		else
		{
			dol_print_error($this->db);
		}

		$this->result = array('nbofelement'=>$num);

		if ($outputmode) return $outarray;
		return $out;
	}


	
	public static function selectarray($htmlname, $array, $id = '', $show_empty = 0, $key_in_label = 0, $value_as_key = 0, $moreparam = '', $translate = 0, $maxlen = 0, $disabled = 0, $sort = '', $morecss = '', $addjscombo = 0, $moreparamonempty = '', $disablebademail = 0, $nohtmlescape = 0)
	{
		global $conf, $langs;

		  
		  
		  
		$jsbeautify = 1;

		if ($value_as_key) $array = array_combine($array, $array);

		$out = '';

		  
		if ($addjscombo && $jsbeautify)
		{
			$minLengthToAutocomplete = 0;
			$tmpplugin = empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) ? (constant('REQUIRE_JQUERY_MULTISELECT') ?constant('REQUIRE_JQUERY_MULTISELECT') : 'select2') : $conf->global->MAIN_USE_JQUERY_MULTISELECT;

			  
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$out .= ajax_combobox($htmlname);
		}

		$out .= '<select id="'.preg_replace('/^\./', '', $htmlname).'" '.($disabled ? 'disabled ' : '').'class="flat '.(preg_replace('/^\./', '', $htmlname)).($morecss ? ' '.$morecss : '').'"';
		$out .= ' name="'.preg_replace('/^\./', '', $htmlname).'" '.($moreparam ? $moreparam : '');
		$out .= '>';

		if ($show_empty)
		{
			$textforempty = ' ';
			if (!empty($conf->use_javascript_ajax)) $textforempty = '&nbsp;';   
			if (!is_numeric($show_empty)) $textforempty = $show_empty;
			$out .= '<option class="optiongrey" '.($moreparamonempty ? $moreparamonempty.' ' : '').'value="'.($show_empty < 0 ? $show_empty : -1).'"'.($id == $show_empty ? ' selected' : '').'>'.$textforempty.'</option>'."\n";
		}

		if (is_array($array))
		{
			  
			if ($translate)
			{
				foreach ($array as $key => $value)
				{
				    if (!is_array($value)) $array[$key] = $langs->trans($value);
				    else $array[$key]['label'] = $langs->trans($value['label']);
				}
			}

			  
			if ($sort == 'ASC') asort($array);
			elseif ($sort == 'DESC') arsort($array);

			foreach ($array as $key => $tmpvalue)
			{
			    if (is_array($tmpvalue)) $value = $tmpvalue['label'];
			    else $value = $tmpvalue;

				$disabled = ''; $style = '';
				if (!empty($disablebademail))
				{
				    if (($disablebademail == 1 && !preg_match('/&lt;.+@.+&gt;/', $value))
				        || ($disablebademail == 2 && preg_match('/---/', $value)))
					{
						$disabled = ' disabled';
						$style = ' class="warning"';
					}
				}

				if ($key_in_label)
				{
					if (empty($nohtmlescape)) $selectOptionValue = dol_escape_htmltag($key.' - '.($maxlen ?dol_trunc($value, $maxlen) : $value));
					else $selectOptionValue = $key.' - '.($maxlen ?dol_trunc($value, $maxlen) : $value);
				}
				else
				{
					if (empty($nohtmlescape)) $selectOptionValue = dol_escape_htmltag($maxlen ?dol_trunc($value, $maxlen) : $value);
					else $selectOptionValue = $maxlen ?dol_trunc($value, $maxlen) : $value;
					if ($value == '' || $value == '-') $selectOptionValue = '&nbsp;';
				}

				$out .= '<option value="'.$key.'"';
				$out .= $style.$disabled;
				if (is_array($id)) {
					if (in_array($key, $id) && !$disabled) $out .= ' selected';   
				} else {
					$id = (string) $id;	  
					if ($id != '' && $id == $key && !$disabled) $out .= ' selected';   
				}
				if ($nohtmlescape) $out .= ' data-html="'.dol_escape_htmltag($selectOptionValue).'"';
				if (is_array($tmpvalue))
				{
				    foreach ($tmpvalue as $keyforvalue => $valueforvalue)
				    {
				        if (preg_match('/^data-/', $keyforvalue)) $out .= ' '.$keyforvalue.'="'.$valueforvalue.'"';
				    }
				}
				$out .= '>';
				  
				$out .= $selectOptionValue;
				$out .= "</option>\n";
			}
		}

		$out .= "</select>";
		return $out;
	}


	
	public static function selectArrayAjax($htmlname, $url, $id = '', $moreparam = '', $moreparamtourl = '', $disabled = 0, $minimumInputLength = 1, $morecss = '', $callurlonselect = 0, $placeholder = '', $acceptdelayedhtml = 0)
	{
		global $conf, $langs;
		global $delayedhtmlcontent;

		  
		if (empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) && !defined('REQUIRE_JQUERY_MULTISELECT')) return '';

		$out = '<select type="text" class="'.$htmlname.($morecss ? ' '.$morecss : '').'" '.($moreparam ? $moreparam.' ' : '').'name="'.$htmlname.'"></select>';

		$tmpplugin = 'select2';
		$outdelayed = "\n".'<!-- JS CODE TO ENABLE '.$tmpplugin.' for id '.$htmlname.' -->
	    	<script>
	    	$(document).ready(function () {

    	        '.($callurlonselect ? 'var saveRemoteData = [];' : '').'

                $(".'.$htmlname.'").select2({
			    	ajax: {
				    	dir: "ltr",
				    	url: "'.$url.'",
				    	dataType: \'json\',
				    	delay: 250,
				    	data: function (params) {
				    		return {
						    	q: params.term, 	  
				    			page: params.page
				    		};
			    		},
			    		processResults: function (data) {
			    			  
			    			  
			    			  
							saveRemoteData = data;
				    	    /* format json result for select2 */
				    	    result = []
				    	    $.each( data, function( key, value ) {
				    	       result.push({id: key, text: value.text});
                            });
			    			  
			    			  
			    			return {results: result, more: false}
			    		},
			    		cache: true
			    	},
	 				language: select2arrayoflanguage,
					containerCssClass: \':all:\',					/* Line to add class of origin SELECT propagated to the new <span class="select2-selection...> tag */
				    placeholder: "'.dol_escape_js($placeholder).'",
			    	escapeMarkup: function (markup) { return markup; }, 	  
			    	minimumInputLength: '.$minimumInputLength.',
			        formatResult: function(result, container, query, escapeMarkup) {
                        return escapeMarkup(result.text);
                    },
			    });

                '.($callurlonselect ? '
                /* Code to execute a GET when we select a value */
                $(".'.$htmlname.'").change(function() {
			    	var selected = $(".'.$htmlname.'").val();
                	console.log("We select in selectArrayAjax the entry "+selected)
			        $(".'.$htmlname.'").val("");  /* reset visible combo value */
    			    $.each( saveRemoteData, function( key, value ) {
    				        if (key == selected)
    			            {
    			                 console.log("selectArrayAjax - Do a redirect to "+value.url)
    			                 location.assign(value.url);
    			            }
                    });
    			});' : '').'

    	   });
	       </script>';

		if ($acceptdelayedhtml)
		{
			$delayedhtmlcontent .= $outdelayed;
		}
		else
		{
			$out .= $outdelayed;
		}
		return $out;
	}

    
	public static function selectArrayFilter($htmlname, $array, $id = '', $moreparam = '', $disableFiltering = 0, $disabled = 0, $minimumInputLength = 1, $morecss = '', $callurlonselect = 0, $placeholder = '', $acceptdelayedhtml = 0)
	{
		global $conf, $langs;
		global $delayedhtmlcontent;

		  
		if (empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) && !defined('REQUIRE_JQUERY_MULTISELECT')) return '';

		$out = '<select type="text" class="'.$htmlname.($morecss ? ' '.$morecss : '').'" '.($moreparam ? $moreparam.' ' : '').'name="'.$htmlname.'"><option></option></select>';

		$formattedarrayresult = array();

		foreach ($array as $key => $value) {
			$o = new stdClass();
			$o->id = $key;
			$o->text = $value['text'];
			$o->url = $value['url'];
			$formattedarrayresult[] = $o;
		}

		$tmpplugin = 'select2';
		$outdelayed = "\n".'<!-- JS CODE TO ENABLE '.$tmpplugin.' for id '.$htmlname.' -->
			<script>
			$(document).ready(function () {
				var data = '.json_encode($formattedarrayresult).';

				'.($callurlonselect ? 'var saveRemoteData = '.json_encode($array).';' : '').'

				$(".'.$htmlname.'").select2({
					data: data,
					language: select2arrayoflanguage,
					containerCssClass: \':all:\',					/* Line to add class of origin SELECT propagated to the new <span class="select2-selection...> tag */
					placeholder: "'.dol_escape_js($placeholder).'",
					escapeMarkup: function (markup) { return markup; }, 	  
					minimumInputLength: '.$minimumInputLength.',
					formatResult: function(result, container, query, escapeMarkup) {
						return escapeMarkup(result.text);
					},
					matcher: function (params, data) {

						if(! data.id) return null;';

		if ($callurlonselect) {
			$outdelayed .= '

						var urlBase = data.url;
						var separ = urlBase.indexOf("?") >= 0 ? "&" : "?";
						/* console.log("params.term="+params.term); */
						/* console.log("params.term encoded="+encodeURIComponent(params.term)); */
						saveRemoteData[data.id].url = urlBase + separ + "sall=" + encodeURIComponent(params.term);';
		}

		if (!$disableFiltering) {
			$outdelayed .= '

						if(data.text.match(new RegExp(params.term))) {
							return data;
						}

						return null;';
		} else {
			$outdelayed .= '

						return data;';
		}

		$outdelayed .= '
					}
				});

				'.($callurlonselect ? '
				/* Code to execute a GET when we select a value */
				$(".'.$htmlname.'").change(function() {
					var selected = $(".'.$htmlname.'").val();
					console.log("We select "+selected)

					$(".'.$htmlname.'").val("");  /* reset visible combo value */
					$.each( saveRemoteData, function( key, value ) {
						if (key == selected)
						{
							console.log("selectArrayAjax - Do a redirect to "+value.url)
							location.assign(value.url);
						}
					});
				});' : '').'

			});
			</script>';

		if ($acceptdelayedhtml)
		{
			$delayedhtmlcontent .= $outdelayed;
		}
		else
		{
			$out .= $outdelayed;
		}
		return $out;
	}

	
	public static function multiselectarray($htmlname, $array, $selected = array(), $key_in_label = 0, $value_as_key = 0, $morecss = '', $translate = 0, $width = 0, $moreattrib = '', $elemtype = '', $placeholder = '', $addjscombo = -1)
	{
		global $conf, $langs;

		$out = '';

		if ($addjscombo < 0) {
		    if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) $addjscombo = 1;
		    else $addjscombo = 0;
		}

		  
		if (!empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) || defined('REQUIRE_JQUERY_MULTISELECT'))
		{
			$out .= "\n".'<!-- JS CODE TO ENABLE '.$tmpplugin.' for id '.$htmlname.' -->
						<script>'."\n";
			if ($addjscombo == 1)
			{
				$tmpplugin = empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) ?constant('REQUIRE_JQUERY_MULTISELECT') : $conf->global->MAIN_USE_JQUERY_MULTISELECT;
				$out .= 'function formatResult(record) {'."\n";
				if ($elemtype == 'category')
				{
					$out .= '	  
									  	return \'<span><img src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> \'+record.text+\'</span>\';';
				}
				else
				{
					$out .= 'return record.text;';
				}
				$out .= '};'."\n";
				$out .= 'function formatSelection(record) {'."\n";
				if ($elemtype == 'category')
				{
					$out .= '	  
									  	return \'<span><img src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> \'+record.text+\'</span>\';';
				}
				else
				{
					$out .= 'return record.text;';
				}
				$out .= '};'."\n";
				$out .= '$(document).ready(function () {
							$(\'#'.$htmlname.'\').'.$tmpplugin.'({
								dir: \'ltr\',
								  
								formatResult: formatResult,
							 	templateResult: formatResult,		/* For 4.0 */
								  
								formatSelection: formatSelection,
							 	templateResult: formatSelection		/* For 4.0 */
							});
						});'."\n";
			}
			elseif ($addjscombo == 2)
			{
				  
				  
				  
				$out .= '$(document).ready(function () {
							$(\'#'.$htmlname.'\').multiSelect({
								containerHTML: \'<div class="multi-select-container">\',
								menuHTML: \'<div class="multi-select-menu">\',
								buttonHTML: \'<span class="multi-select-button '.$morecss.'">\',
								menuItemHTML: \'<label class="multi-select-menuitem">\',
								activeClass: \'multi-select-container--open\',
								noneText: \''.$placeholder.'\'
							});
						})';
			}
			$out .= '</script>';
		}

		  
		$out .= '<select id="'.$htmlname.'" class="multiselect'.($morecss ? ' '.$morecss : '').'" multiple name="'.$htmlname.'[]"'.($moreattrib ? ' '.$moreattrib : '').($width ? ' style="width: '.(preg_match('/%/', $width) ? $width : $width.'px').'"' : '').'>'."\n";
		if (is_array($array) && !empty($array))
		{
			if ($value_as_key) $array = array_combine($array, $array);

			if (!empty($array))
			{
				foreach ($array as $key => $value)
				{
					$out .= '<option value="'.$key.'"';
                    if (is_array($selected) && !empty($selected) && in_array((string) $key, $selected) && ((string) $key != ''))
					{
						$out .= ' selected';
					}
					$out .= '>';

					$newval = ($translate ? $langs->trans($value) : $value);
					$newval = ($key_in_label ? $key.' - '.$newval : $newval);
					$out .= dol_htmlentitiesbr($newval);
					$out .= '</option>'."\n";
				}
			}
		}
		$out .= '</select>'."\n";

		return $out;
	}


	
	public static function multiSelectArrayWithCheckbox($htmlname, &$array, $varpage)
	{
		global $conf, $langs, $user;

		if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) return '';

		$tmpvar = "MAIN_SELECTEDFIELDS_".$varpage;
		if (!empty($user->conf->$tmpvar))
		{
			$tmparray = explode(',', $user->conf->$tmpvar);
			foreach ($array as $key => $val)
			{
				  
				  
				if (in_array($key, $tmparray)) $array[$key]['checked'] = 1;
				else $array[$key]['checked'] = 0;
			}
		}

		$lis = '';
		$listcheckedstring = '';

		foreach ($array as $key => $val)
		{
		    
		    if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled'])
		    {
			    unset($array[$key]);   
			    continue;
		    }
		    if ($val['label'])
		    {
		        $lis .= '<li><input type="checkbox" id="checkbox'.$key.'" value="'.$key.'"'.(empty($val['checked']) ? '' : ' checked="checked"').'/><label for="checkbox'.$key.'">'.dol_escape_htmltag($langs->trans($val['label'])).'</label></li>';
			    $listcheckedstring .= (empty($val['checked']) ? '' : $key.',');
		    }
		}

		$out = '<!-- Component multiSelectArrayWithCheckbox '.$htmlname.' -->

        <dl class="dropdown">
            <dt>
            <a href="#'.$htmlname.'">
              '.img_picto('', 'list').'
            </a>
            <input type="hidden" class="'.$htmlname.'" name="'.$htmlname.'" value="'.$listcheckedstring.'">
            </dt>
            <dd class="dropdowndd">
                <div class="multiselectcheckbox'.$htmlname.'">
                    <ul class="ul'.$htmlname.'">
                    '.$lis.'
                    </ul>
                </div>
            </dd>
        </dl>

        <script type="text/javascript">
          jQuery(document).ready(function () {
              $(\'.multiselectcheckbox'.$htmlname.' input[type="checkbox"]\').on(\'click\', function () {
                  console.log("A new field was added/removed")
                  $("input:hidden[name=formfilteraction]").val(\'listafterchangingselectedfields\')
                  var title = $(this).val() + ",";
                  if ($(this).is(\':checked\')) {
                      $(\'.'.$htmlname.'\').val(title + $(\'.'.$htmlname.'\').val());
                  }
                  else {
                      $(\'.'.$htmlname.'\').val( $(\'.'.$htmlname.'\').val().replace(title, \'\') )
                  }
                    
                  $(this).parents(\'form:first\').submit();
              });
           });
        </script>

        ';
		return $out;
	}

	
    public function showCategories($id, $type, $rendermode = 0)
	{
		global $db;

		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		$cat = new Categorie($db);
		$categories = $cat->containing($id, $type);

		if ($rendermode == 1)
		{
			$toprint = array();
			foreach ($categories as $c)
			{
				$ways = $c->print_all_ways();   
				foreach ($ways as $way)
				{
					$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"'.($c->color ? ' style="background: #'.$c->color.';"' : ' style="background: #aaa"').'>'.img_object('', 'category').' '.$way.'</li>';
				}
			}
			return '<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">'.implode(' ', $toprint).'</ul></div>';
		}

		if ($rendermode == 0)
		{
			$arrayselected = array();
			$cate_arbo = $this->select_all_categories($type, '', 'parent', 64, 0, 1);
			foreach ($categories as $c) {
				$arrayselected[] = $c->id;
			}

			return $this->multiselectarray('categories', $cate_arbo, $arrayselected, '', 0, '', 0, '100%', 'disabled', 'category');
		}

		return 'ErrorBadValueForParameterRenderMode';   
	}

	
    public function showLinkedObjectBlock($object, $morehtmlright = '', $compatibleImportElementsList = false)
	{
		global $conf, $langs, $hookmanager;
		global $bc, $action;

		$object->fetchObjectLinked();

		  
		$hookmanager->initHooks(array('commonobject'));
		$parameters = array(
			'morehtmlright' => $morehtmlright,
		    'compatibleImportElementsList' => &$compatibleImportElementsList,
		);
		$reshook = $hookmanager->executeHooks('showLinkedObjectBlock', $parameters, $object, $action);   

		if (empty($reshook))
		{
			$nbofdifferenttypes = count($object->linkedObjects);

			print '<!-- showLinkedObjectBlock -->';
			print load_fiche_titre($langs->trans('RelatedObjects'), $morehtmlright, '', 0, 0, 'showlinkedobjectblock');


			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder allwidth" data-block="showLinkedObject" data-element="'.$object->element.'"  data-elementid="'.$object->id.'"   >';

			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Type").'</td>';
			print '<td>'.$langs->trans("Ref").'</td>';
			print '<td class="center"></td>';
			print '<td class="center">'.$langs->trans("Date").'</td>';
			print '<td class="right">'.$langs->trans("AmountHTShort").'</td>';
			print '<td class="right">'.$langs->trans("Status").'</td>';
			print '<td></td>';
			print '</tr>';

			$nboftypesoutput = 0;

			foreach ($object->linkedObjects as $objecttype => $objects)
			{
				$tplpath = $element = $subelement = $objecttype;

				  
				$showImportButton = false;
				if (!empty($compatibleImportElementsList) && in_array($element, $compatibleImportElementsList)) {
				    $showImportButton = true;
				}

				$regs = array();
				if ($objecttype != 'supplier_proposal' && preg_match('/^([^_]+)_([^_]+)/i', $objecttype, $regs))
				{
					$element = $regs[1];
					$subelement = $regs[2];
					$tplpath = $element.'/'.$subelement;
				}
				$tplname = 'linkedobjectblock';

				  
				if ($objecttype == 'facture') {
					$tplpath = 'compta/'.$element;
					if (empty($conf->facture->enabled)) continue;   
				}
				elseif ($objecttype == 'facturerec') {
					$tplpath = 'compta/facture';
					$tplname = 'linkedobjectblockForRec';
					if (empty($conf->facture->enabled)) continue;   
				}
				elseif ($objecttype == 'propal') {
					$tplpath = 'comm/'.$element;
					if (empty($conf->propal->enabled)) continue;   
				}
				elseif ($objecttype == 'supplier_proposal') {
					if (empty($conf->supplier_proposal->enabled)) continue;   
				}
				elseif ($objecttype == 'shipping' || $objecttype == 'shipment') {
					$tplpath = 'expedition';
					if (empty($conf->expedition->enabled)) continue;   
				}
        		elseif ($objecttype == 'reception') {
        			$tplpath = 'reception';
        			if (empty($conf->reception->enabled)) continue;   
        		}
				elseif ($objecttype == 'delivery') {
					$tplpath = 'livraison';
					if (empty($conf->expedition->enabled)) continue;   
				}
				elseif ($objecttype == 'invoice_supplier') {
					$tplpath = 'fourn/facture';
				}
				elseif ($objecttype == 'order_supplier') {
					$tplpath = 'fourn/commande';
				}
				elseif ($objecttype == 'expensereport') {
					$tplpath = 'expensereport';
				}
				elseif ($objecttype == 'subscription') {
					$tplpath = 'adherents';
				}

				global $linkedObjectBlock;
				$linkedObjectBlock = $objects;


				  
				$dirtpls = array_merge($conf->modules_parts['tpl'], array('/'.$tplpath.'/tpl'));
				foreach ($dirtpls as $reldir)
				{
					if ($nboftypesoutput == ($nbofdifferenttypes - 1))      
					{
						global $noMoreLinkedObjectBlockAfter;
						$noMoreLinkedObjectBlockAfter = 1;
					}

					$res = @include dol_buildpath($reldir.'/'.$tplname.'.tpl.php');
					if ($res)
					{
						$nboftypesoutput++;
						break;
					}
				}
			}

			if (!$nboftypesoutput)
			{
				print '<tr><td class="impair opacitymedium" colspan="7">'.$langs->trans("None").'</td></tr>';
			}

			print '</table>';

			if (!empty($compatibleImportElementsList))
			{
			    $res = @include dol_buildpath('core/tpl/ajax/objectlinked_lineimport.tpl.php');
			}


			print '</div>';

			return $nbofdifferenttypes;
		}
	}

	
    public function showLinkToObjectBlock($object, $restrictlinksto = array(), $excludelinksto = array())
	{
		global $conf, $langs, $hookmanager;
		global $bc, $action;

		$linktoelem = '';
		$linktoelemlist = '';
		$listofidcompanytoscan = '';

		if (!is_object($object->thirdparty)) $object->fetch_thirdparty();

		$possiblelinks = array();
		if (is_object($object->thirdparty) && !empty($object->thirdparty->id) && $object->thirdparty->id > 0)
		{
			$listofidcompanytoscan = $object->thirdparty->id;
			if (($object->thirdparty->parent > 0) && !empty($conf->global->THIRDPARTY_INCLUDE_PARENT_IN_LINKTO)) $listofidcompanytoscan .= ','.$object->thirdparty->parent;
			if (($object->fk_project > 0) && !empty($conf->global->THIRDPARTY_INCLUDE_PROJECT_THIRDPARY_IN_LINKTO))
			{
				include_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
				$tmpproject = new Project($this->db);
				$tmpproject->fetch($object->fk_project);
				if ($tmpproject->socid > 0 && ($tmpproject->socid != $object->thirdparty->id)) $listofidcompanytoscan .= ','.$tmpproject->socid;
				unset($tmpproject);
			}

			$possiblelinks = array(
				'propal'=>array('enabled'=>$conf->propal->enabled, 'perms'=>1, 'label'=>'LinkToProposal', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_client, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('propal').')'),
				'order'=>array('enabled'=>$conf->commande->enabled, 'perms'=>1, 'label'=>'LinkToOrder', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_client, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('commande').')'),
				'invoice'=>array('enabled'=>$conf->facture->enabled, 'perms'=>1, 'label'=>'LinkToInvoice', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_client, t.total as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('invoice').')'),
				'invoice_template'=>array('enabled'=>$conf->facture->enabled, 'perms'=>1, 'label'=>'LinkToTemplateInvoice', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.titre as ref, t.total as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_rec as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('invoice').')'),
				'contrat'=>array('enabled'=>$conf->contrat->enabled, 'perms'=>1, 'label'=>'LinkToContract', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, '' as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."contrat as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('contract').')'),
				'fichinter'=>array('enabled'=>$conf->ficheinter->enabled, 'perms'=>1, 'label'=>'LinkToIntervention', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."fichinter as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('intervention').')'),
				'supplier_proposal'=>array('enabled'=>$conf->supplier_proposal->enabled, 'perms'=>1, 'label'=>'LinkToSupplierProposal', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, '' as ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."supplier_proposal as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('supplier_proposal').')'),
				'order_supplier'=>array('enabled'=>$conf->supplier_order->enabled, 'perms'=>1, 'label'=>'LinkToSupplierOrder', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande_fournisseur as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('commande_fournisseur').')'),
				'invoice_supplier'=>array('enabled'=>$conf->supplier_invoice->enabled, 'perms'=>1, 'label'=>'LinkToSupplierInvoice', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.ref_supplier, t.total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('facture_fourn').')'),
				'ticket'=>array('enabled'=>$conf->ticket->enabled, 'perms'=>1, 'label'=>'LinkToTicket', 'sql'=>"SELECT s.rowid as socid, s.nom as name, s.client, t.rowid, t.ref, t.track_id, '0' as total_ht FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."ticket as t WHERE t.fk_soc = s.rowid AND t.fk_soc IN (".$listofidcompanytoscan.') AND t.entity IN ('.getEntity('ticket').')')
			);
		}

		  
		$hookmanager->initHooks(array('commonobject'));
		$parameters = array('listofidcompanytoscan' => $listofidcompanytoscan);

		if (!empty($listofidcompanytoscan))    
		{
            $reshook = $hookmanager->executeHooks('showLinkToObjectBlock', $parameters, $object, $action);   
		}

		if (empty($reshook))
		{
			if (is_array($hookmanager->resArray) && count($hookmanager->resArray))
			{
				$possiblelinks = array_merge($possiblelinks, $hookmanager->resArray);
			}
		}
		elseif ($reshook > 0)
		{
			if (is_array($hookmanager->resArray) && count($hookmanager->resArray))
			{
				$possiblelinks = $hookmanager->resArray;
			}
		}

		foreach ($possiblelinks as $key => $possiblelink)
		{
			$num = 0;

			if (empty($possiblelink['enabled'])) continue;

			if (!empty($possiblelink['perms']) && (empty($restrictlinksto) || in_array($key, $restrictlinksto)) && (empty($excludelinksto) || !in_array($key, $excludelinksto)))
			{
				print '<div id="'.$key.'list"'.(empty($conf->use_javascript_ajax) ? '' : ' style="display:none"').'>';
				$sql = $possiblelink['sql'];

				$resqllist = $this->db->query($sql);
				if ($resqllist)
				{
					$num = $this->db->num_rows($resqllist);
					$i = 0;

					print '<br>';
					print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST" name="formlinked'.$key.'">';
					print '<input type="hidden" name="action" value="addlink">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="id" value="'.$object->id.'">';
					print '<input type="hidden" name="addlink" value="'.$key.'">';
					print '<table class="noborder">';
					print '<tr class="liste_titre">';
					print '<td class="nowrap"></td>';
					print '<td class="center">'.$langs->trans("Ref").'</td>';
					print '<td class="left">'.$langs->trans("RefCustomer").'</td>';
					print '<td class="right">'.$langs->trans("AmountHTShort").'</td>';
					print '<td class="left">'.$langs->trans("Company").'</td>';
					print '</tr>';
					while ($i < $num)
					{
						$objp = $this->db->fetch_object($resqllist);

						print '<tr class="oddeven">';
						print '<td class="left">';
						print '<input type="radio" name="idtolinkto" value='.$objp->rowid.'>';
						print '</td>';
						print '<td class="center">'.$objp->ref.'</td>';
						print '<td>'.$objp->ref_client.'</td>';
						print '<td class="right">'.price($objp->total_ht).'</td>';
						print '<td>'.$objp->name.'</td>';
						print '</tr>';
						$i++;
					}
					print '</table>';
					print '<div class="center"><input type="submit" class="button valignmiddle" value="'.$langs->trans('ToLink').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'"></div>';

					print '</form>';
					$this->db->free($resqllist);
				} else {
					dol_print_error($this->db);
				}
				print '</div>';
				if ($num > 0)
				{
				}

				  
				if ($num > 0) $linktoelemlist .= '<li><a href="#linkto'.$key.'" class="linkto dropdowncloseonclick" rel="'.$key.'">'.$langs->trans($possiblelink['label']).' ('.$num.')</a></li>';
				  
				else $linktoelemlist .= '<li><span class="linktodisabled">'.$langs->trans($possiblelink['label']).' (0)</span></li>';
			}
		}

		if ($linktoelemlist)
		{
			$linktoelem = '
    		<dl class="dropdown" id="linktoobjectname">
    		';
			if (!empty($conf->use_javascript_ajax)) $linktoelem .= '<dt><a href="#linktoobjectname">'.$langs->trans("LinkTo").'...</a></dt>';
			$linktoelem .= '<dd>
    		<div class="multiselectlinkto">
    		<ul class="ulselectedfields">'.$linktoelemlist.'
    		</ul>
    		</div>
    		</dd>
    		</dl>';
		}
		else
		{
			$linktoelem = '';
		}

		if (!empty($conf->use_javascript_ajax))
		{
		    print '<!-- Add js to show linkto box -->
				<script>
				jQuery(document).ready(function() {
					jQuery(".linkto").click(function() {
						console.log("We choose to show/hide link for rel="+jQuery(this).attr(\'rel\'));
					    jQuery("#"+jQuery(this).attr(\'rel\')+"list").toggle();
						jQuery(this).toggle();
					});
				});
				</script>
		    ';
		}

		return $linktoelem;
	}

	
    public function selectyesno($htmlname, $value = '', $option = 0, $disabled = false, $useempty = 0)
	{
		global $langs;

		$yes = "yes"; $no = "no";
		if ($option)
		{
			$yes = "1";
			$no = "0";
		}

		$disabled = ($disabled ? ' disabled' : '');

		$resultyesno = '<select class="flat width75" id="'.$htmlname.'" name="'.$htmlname.'"'.$disabled.'>'."\n";
		if ($useempty) $resultyesno .= '<option value="-1"'.(($value < 0) ? ' selected' : '').'>&nbsp;</option>'."\n";
		if (("$value" == 'yes') || ($value == 1))
		{
			$resultyesno .= '<option value="'.$yes.'" selected>'.$langs->trans("Yes").'</option>'."\n";
			$resultyesno .= '<option value="'.$no.'">'.$langs->trans("No").'</option>'."\n";
		}
		else
	    {
	   		$selected = (($useempty && $value != '0' && $value != 'no') ? '' : ' selected');
			$resultyesno .= '<option value="'.$yes.'">'.$langs->trans("Yes").'</option>'."\n";
			$resultyesno .= '<option value="'.$no.'"'.$selected.'>'.$langs->trans("No").'</option>'."\n";
		}
		$resultyesno .= '</select>'."\n";
		return $resultyesno;
	}

      
	
    public function select_export_model($selected = '', $htmlname = 'exportmodelid', $type = '', $useempty = 0)
	{
          
		$sql = "SELECT rowid, label";
		$sql .= " FROM ".MAIN_DB_PREFIX."export_model";
		$sql .= " WHERE type = '".$type."'";
		$sql .= " ORDER BY rowid";
		$result = $this->db->query($sql);
		if ($result)
		{
			print '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';
			if ($useempty)
			{
				print '<option value="-1">&nbsp;</option>';
			}

			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($result);
				if ($selected == $obj->rowid)
				{
					print '<option value="'.$obj->rowid.'" selected>';
				}
				else
				{
					print '<option value="'.$obj->rowid.'">';
				}
				print $obj->label;
				print '</option>';
				$i++;
			}
			print "</select>";
		}
		else {
			dol_print_error($this->db);
		}
	}

	
    public function showrefnav($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $morehtmlright = '')
	{
		global $langs, $conf, $hookmanager;

		$ret = '';
		if (empty($fieldid))  $fieldid = 'rowid';
		if (empty($fieldref)) $fieldref = 'ref';

		  
		if (is_object($hookmanager))
		{
			$parameters = array();
			$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object);   
			$object->next_prev_filter .= $hookmanager->resPrint;
		}
		$previous_ref = $next_ref = '';
		if ($shownav)
		{
			  
			$object->load_previous_next_ref((isset($object->next_prev_filter) ? $object->next_prev_filter : ''), $fieldid, $nodbprefix);

			$navurl = $_SERVER["PHP_SELF"];
			  
			if ($paramid == 'project_ref')
			{
			    if (preg_match('/\/tasks\/(task|contact|note|document)\.php/', $navurl))       
			    {
				    $navurl = preg_replace('/\/tasks\/(task|contact|time|note|document)\.php/', '/tasks.php', $navurl);
    				$paramid = 'ref';
			    }
			}

			  
			  
			$stringforfirstkey = $langs->trans("KeyboardShortcut");
			if ($conf->browser->name == 'chrome')
			{
				$stringforfirstkey .= ' ALT +';
			}
			elseif ($conf->browser->name == 'firefox')
			{
				$stringforfirstkey .= ' ALT + SHIFT +';
			}
			else
			{
				$stringforfirstkey .= ' CTL +';
			}

			$previous_ref = $object->ref_previous ? '<a accesskey="p" title="'.$stringforfirstkey.' p" class="classfortooltip" href="'.$navurl.'?'.$paramid.'='.urlencode($object->ref_previous).$moreparam.'"><i class="fa fa-chevron-left"></i></a>' : '<span class="inactive"><i class="fa fa-chevron-left opacitymedium"></i></span>';
			$next_ref     = $object->ref_next ? '<a accesskey="n" title="'.$stringforfirstkey.' n" class="classfortooltip" href="'.$navurl.'?'.$paramid.'='.urlencode($object->ref_next).$moreparam.'"><i class="fa fa-chevron-right"></i></a>' : '<span class="inactive"><i class="fa fa-chevron-right opacitymedium"></i></span>';
		}

		  
		$ret .= '<!-- Start banner content --><div style="vertical-align: middle">';

		  
		if ($morehtmlright) $ret .= '<div class="inline-block floatleft">'.$morehtmlright.'</div>';

		if ($previous_ref || $next_ref || $morehtml)
		{
			$ret .= '<div class="pagination paginationref"><ul class="right">';
		}
		if ($morehtml)
		{
			$ret .= '<li class="noborder litext">'.$morehtml.'</li>';
		}
		if ($shownav && ($previous_ref || $next_ref))
		{
			$ret .= '<li class="pagination">'.$previous_ref.'</li>';
			$ret .= '<li class="pagination">'.$next_ref.'</li>';
		}
		if ($previous_ref || $next_ref || $morehtml)
		{
			$ret .= '</ul></div>';
		}

		$parameters = array();
		$reshook = $hookmanager->executeHooks('moreHtmlStatus', $parameters, $object);   
		if (empty($reshook)) $morehtmlstatus .= $hookmanager->resPrint;
		else $morehtmlstatus = $hookmanager->resPrint;
		if ($morehtmlstatus) $ret .= '<div class="statusref">'.$morehtmlstatus.'</div>';

		$parameters = array();
		$reshook = $hookmanager->executeHooks('moreHtmlRef', $parameters, $object);   
		if (empty($reshook)) $morehtmlref .= $hookmanager->resPrint;
		elseif ($reshook > 0) $morehtmlref = $hookmanager->resPrint;

		  
		if ($morehtmlleft)
		{
			if ($conf->browser->layout == 'phone') $ret .= '<!-- morehtmlleft --><div class="floatleft">'.$morehtmlleft.'</div>';   
			else $ret .= '<!-- morehtmlleft --><div class="inline-block floatleft">'.$morehtmlleft.'</div>';
		}

		  
		$ret .= '<div class="inline-block floatleft valignmiddle refid'.(($shownav && ($previous_ref || $next_ref)) ? ' refidpadding' : '').'">';

		  
		if ($object->element == 'societe')
		{
			$ret .= dol_htmlentities($object->name);
		}
		elseif ($object->element == 'member')
		{
			$ret .= $object->ref.'<br>';
			$fullname = $object->getFullName($langs);
			if ($object->morphy == 'mor' && $object->societe) {
				$ret .= dol_htmlentities($object->societe).((!empty($fullname) && $object->societe != $fullname) ? ' ('.dol_htmlentities($fullname).')' : '');
			} else {
				$ret .= dol_htmlentities($fullname).((!empty($object->societe) && $object->societe != $fullname) ? ' ('.dol_htmlentities($object->societe).')' : '');
			}
		}
		elseif (in_array($object->element, array('contact', 'user', 'usergroup')))
		{
			$ret .= dol_htmlentities($object->getFullName($langs));
		}
		elseif (in_array($object->element, array('action', 'agenda')))
		{
			$ret .= $object->ref.'<br>'.$object->label;
		}
		elseif (in_array($object->element, array('adherent_type')))
		{
			$ret .= $object->label;
		}
		elseif ($object->element == 'ecm_directories')
		{
			$ret .= '';
		}
		elseif ($fieldref != 'none') $ret .= dol_htmlentities($object->$fieldref);


		if ($morehtmlref)
		{
			$ret .= ' '.$morehtmlref;
		}
		$ret .= '</div>';

		$ret .= '</div><!-- End banner content -->';

		return $ret;
	}


	
    public function showbarcode(&$object, $width = 100)
	{
		global $conf;

		  
		if (empty($object->barcode)) return '';

		  
		if (empty($object->barcode_type_code) || empty($object->barcode_type_coder))
		{
			$result = $object->fetch_barcode();
			  
			if ($result < 1) return '<!-- ErrorFetchBarcode -->';
		}

		  
		$url = DOL_URL_ROOT.'/viewimage.php?modulepart=barcode&generator='.urlencode($object->barcode_type_coder).'&code='.urlencode($object->barcode).'&encoding='.urlencode($object->barcode_type_code);
		$out = '<!-- url barcode = '.$url.' -->';
		$out .= '<img src="'.$url.'">';
		return $out;
	}

	
	public static function showphoto($modulepart, $object, $width = 100, $height = 0, $caneditfield = 0, $cssclass = 'photowithmargin', $imagesize = '', $addlinktofullsize = 1, $cache = 0, $forcecapture = '')
	{
		global $conf, $langs;

		$entity = (!empty($object->entity) ? $object->entity : $conf->entity);
		$id = (!empty($object->id) ? $object->id : $object->rowid);

		$ret = ''; $dir = ''; $file = ''; $originalfile = ''; $altfile = ''; $email = ''; $capture = '';
		if ($modulepart == 'societe')
		{
			$dir = $conf->societe->multidir_output[$entity];
			if (!empty($object->logo))
			{
				if ((string) $imagesize == 'mini') $file = get_exdir(0, 0, 0, 0, $object, 'thirdparty').'/logos/'.getImageFileNameForSize($object->logo, '_mini');   
				elseif ((string) $imagesize == 'small') $file = get_exdir(0, 0, 0, 0, $object, 'thirdparty').'/logos/'.getImageFileNameForSize($object->logo, '_small');
				else $file = get_exdir(0, 0, 0, 0, $object, 'thirdparty').'/logos/'.$object->logo;
				$originalfile = get_exdir(0, 0, 0, 0, $object, 'thirdparty').'/logos/'.$object->logo;
			}
			$email = $object->email;
		}
		elseif ($modulepart == 'contact')
		{
			$dir = $conf->societe->multidir_output[$entity].'/contact';
			if (!empty($object->photo))
			{
				if ((string) $imagesize == 'mini') $file = get_exdir(0, 0, 0, 0, $object, 'contact').'/photos/'.getImageFileNameForSize($object->photo, '_mini');
				elseif ((string) $imagesize == 'small') $file = get_exdir(0, 0, 0, 0, $object, 'contact').'/photos/'.getImageFileNameForSize($object->photo, '_small');
				else $file = get_exdir(0, 0, 0, 0, $object, 'contact').'/photos/'.$object->photo;
				$originalfile = get_exdir(0, 0, 0, 0, $object, 'contact').'/photos/'.$object->photo;
			}
			$email = $object->email;
			$capture = 'user';
		}
		elseif ($modulepart == 'userphoto')
		{
			$dir = $conf->user->dir_output;
			if (!empty($object->photo))
			{
				if ((string) $imagesize == 'mini') $file = get_exdir(0, 0, 0, 0, $object, 'user').$object->id.'/'.getImageFileNameForSize($object->photo, '_mini');
				elseif ((string) $imagesize == 'small') $file = get_exdir(0, 0, 0, 0, $object, 'user').$object->id.'/'.getImageFileNameForSize($object->photo, '_small');
				else $file = get_exdir(0, 0, 0, 0, $object, 'user').'/'.$object->id.'/'.$object->photo;
				$originalfile = get_exdir(0, 0, 0, 0, $object, 'user').'/'.$object->id.'/'.$object->photo;
			}
			if (!empty($conf->global->MAIN_OLD_IMAGE_LINKS)) $altfile = $object->id.".jpg";   
			$email = $object->email;
			$capture = 'user';
		}
		elseif ($modulepart == 'memberphoto')
		{
			$dir = $conf->adherent->dir_output;
			if (!empty($object->photo))
			{
				if ((string) $imagesize == 'mini') $file = get_exdir(0, 0, 0, 0, $object, 'member').'photos/'.getImageFileNameForSize($object->photo, '_mini');
				elseif ((string) $imagesize == 'small') $file = get_exdir(0, 0, 0, 0, $object, 'member').'photos/'.getImageFileNameForSize($object->photo, '_small');
				else $file = get_exdir(0, 0, 0, 0, $object, 'member').'photos/'.$object->photo;
				$originalfile = get_exdir(0, 0, 0, 0, $object, 'member').'photos/'.$object->photo;
			}
			if (!empty($conf->global->MAIN_OLD_IMAGE_LINKS)) $altfile = $object->id.".jpg";   
			$email = $object->email;
			$capture = 'user';
		}
		else
		{
			  
			$dir = $conf->$modulepart->dir_output;
			if (!empty($object->photo))
			{
				if ((string) $imagesize == 'mini') $file = get_exdir($id, 2, 0, 0, $object, $modulepart).'photos/'.getImageFileNameForSize($object->photo, '_mini');
				elseif ((string) $imagesize == 'small') $file = get_exdir($id, 2, 0, 0, $object, $modulepart).'photos/'.getImageFileNameForSize($object->photo, '_small');
				else $file = get_exdir($id, 2, 0, 0, $object, $modulepart).'photos/'.$object->photo;
				$originalfile = get_exdir($id, 2, 0, 0, $object, $modulepart).'photos/'.$object->photo;
			}
			if (!empty($conf->global->MAIN_OLD_IMAGE_LINKS)) $altfile = $object->id.".jpg";   
			$email = $object->email;
		}

		if ($forcecapture) $capture = $forcecapture;

		if ($dir)
		{
			if ($file && file_exists($dir."/".$file))
			{
				if ($addlinktofullsize)
				{
					$urladvanced = getAdvancedPreviewUrl($modulepart, $originalfile, 0, '&entity='.$entity);
					if ($urladvanced) $ret .= '<a href="'.$urladvanced.'">';
					else $ret .= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$entity.'&file='.urlencode($originalfile).'&cache='.$cache.'">';
				}
				$ret .= '<img alt="Photo" class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').' photologo'.(preg_replace('/[^a-z]/i', '_', $file)).'" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$entity.'&file='.urlencode($file).'&cache='.$cache.'">';
				if ($addlinktofullsize) $ret .= '</a>';
			}
			elseif ($altfile && file_exists($dir."/".$altfile))
			{
				if ($addlinktofullsize)
				{
					$urladvanced = getAdvancedPreviewUrl($modulepart, $originalfile, 0, '&entity='.$entity);
					if ($urladvanced) $ret .= '<a href="'.$urladvanced.'">';
					else $ret .= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$entity.'&file='.urlencode($originalfile).'&cache='.$cache.'">';
				}
				$ret .= '<img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="Photo alt" id="photologo'.(preg_replace('/[^a-z]/i', '_', $file)).'" class="'.$cssclass.'" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$modulepart.'&entity='.$entity.'&file='.urlencode($altfile).'&cache='.$cache.'">';
				if ($addlinktofullsize) $ret .= '</a>';
			}
			else
			{
				$nophoto = '/public/theme/common/nophoto.png';
				if (in_array($modulepart, array('userphoto', 'contact', 'memberphoto')))	  
				{
					if ($modulepart == 'memberphoto' && strpos($object->morphy, 'mor') !== false) {
						$nophoto = '/public/theme/common/company.png';
					}
					else {
						$nophoto = '/public/theme/common/user_anonymous.png';
						if ($object->gender == 'man') $nophoto = '/public/theme/common/user_man.png';
						if ($object->gender == 'woman') $nophoto = '/public/theme/common/user_woman.png';
					}
				}

				if (!empty($conf->gravatar->enabled) && $email)
				{
					
					global $dolibarr_main_url_root;
					$ret .= '<!-- Put link to gravatar -->';
					  
					$defaultimg = 'mm';
					$ret .= '<img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="Gravatar avatar" title="'.$email.' Gravatar avatar" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="https://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s='.$width.'&d='.$defaultimg.'">'; // gravatar need md5 hash
				}
				else
				{
					$ret .= '<img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="'.DOL_URL_ROOT.$nophoto.'">';
				}
			}

			if ($caneditfield)
			{
				if ($object->photo) $ret .= "<br>\n";
				$ret .= '<table class="nobordernopadding centpercent">';
				if ($object->photo) $ret .= '<tr><td><input type="checkbox" class="flat photodelete" name="deletephoto" id="photodelete"> '.$langs->trans("Delete").'<br><br></td></tr>';
				$ret .= '<tr><td class="tdoverflow"><input type="file" class="flat maxwidth200onsmartphone" name="photo" id="photoinput" accept="image/*"'.($capture ? ' capture="'.$capture.'"' : '').'></td></tr>';
				$ret .= '</table>';
			}
		}
		else dol_print_error('', 'Call of showphoto with wrong parameters modulepart='.$modulepart);

		return $ret;
	}

      
	
    public function select_dolgroups($selected = '', $htmlname = 'groupid', $show_empty = 0, $exclude = '', $disabled = 0, $include = '', $enableonly = '', $force_entity = '0', $multiple = false)
	{
          
		global $conf, $user, $langs;

		  
		if (is_array($exclude))	$excludeGroups = implode("','", $exclude);
		  
		if (is_array($include))	$includeGroups = implode("','", $include);

		if (!is_array($selected)) $selected = array($selected);

		$out = '';

		  
		$sql = "SELECT ug.rowid, ug.nom as name";
		if (!empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && !$user->entity)
		{
			$sql .= ", e.label";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX."usergroup as ug ";
		if (!empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && !$user->entity)
		{
			$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."entity as e ON e.rowid=ug.entity";
			if ($force_entity) $sql .= " WHERE ug.entity IN (0,".$force_entity.")";
			else $sql .= " WHERE ug.entity IS NOT NULL";
		}
		else
		{
			$sql .= " WHERE ug.entity IN (0,".$conf->entity.")";
		}
		if (is_array($exclude) && $excludeGroups) $sql .= " AND ug.rowid NOT IN ('".$excludeGroups."')";
		if (is_array($include) && $includeGroups) $sql .= " AND ug.rowid IN ('".$includeGroups."')";
		$sql .= " ORDER BY ug.nom ASC";

		dol_syslog(get_class($this)."::select_dolgroups", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			  
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
		   	$out .= ajax_combobox($htmlname);

			$out .= '<select class="flat minwidth200" id="'.$htmlname.'" name="'.$htmlname.($multiple ? '[]' : '').'" '.($multiple ? 'multiple' : '').' '.($disabled ? ' disabled' : '').'>';

			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				if ($show_empty && !$multiple) $out .= '<option value="-1"'.(in_array(-1, $selected) ? ' selected' : '').'>&nbsp;</option>'."\n";

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$disableline = 0;
					if (is_array($enableonly) && count($enableonly) && !in_array($obj->rowid, $enableonly)) $disableline = 1;

					$out .= '<option value="'.$obj->rowid.'"';
					if ($disableline) $out .= ' disabled';
					if ((is_object($selected[0]) && $selected[0]->id == $obj->rowid) || (!is_object($selected[0]) && in_array($obj->rowid, $selected)))
					{
						$out .= ' selected';
					}
					$out .= '>';

					$out .= $obj->name;
					if (!empty($conf->multicompany->enabled) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1)
					{
						$out .= " (".$obj->label.")";
					}

					$out .= '</option>';
					$i++;
				}
			}
			else
			{
				if ($show_empty) $out .= '<option value="-1"'.(in_array(-1, $selected) ? ' selected' : '').'></option>'."\n";
				$out .= '<option value="" disabled>'.$langs->trans("NoUserGroupDefined").'</option>';
			}
			$out .= '</select>';
		}
		else
		{
			dol_print_error($this->db);
		}

		return $out;
	}


	
    public function showFilterButtons()
	{
		$out = '<div class="nowrap">';
		$out .= '<button type="submit" class="liste_titre button_search" name="button_search_x" value="x"><span class="fa fa-search"></span></button>';
		$out .= '<button type="submit" class="liste_titre button_removefilter" name="button_removefilter_x" value="x"><span class="fa fa-remove"></span></button>';
		$out .= '</div>';

		return $out;
	}

	
    public function showCheckAddButtons($cssclass = 'checkforaction', $calljsfunction = 0)
	{
		global $conf, $langs;

		$out = '';
		if (!empty($conf->use_javascript_ajax)) $out .= '<div class="inline-block checkallactions"><input type="checkbox" id="checkallactions" name="checkallactions" class="checkallactions"></div>';
		$out .= '<script>
            $(document).ready(function() {
            	$("#checkallactions").click(function() {
                    if($(this).is(\':checked\')){
                        console.log("We check all");
                		$(".'.$cssclass.'").prop(\'checked\', true).trigger(\'change\');
                    }
                    else
                    {
                        console.log("We uncheck all");
                		$(".'.$cssclass.'").prop(\'checked\', false).trigger(\'change\');
                    }'."\n";
		if ($calljsfunction) $out .= 'if (typeof initCheckForSelect == \'function\') { initCheckForSelect(0); } else { console.log("No function initCheckForSelect found. Call won\'t be done."); }';
		$out .= '         });

        	$(".checkforselect").change(function() {
				$(this).closest("tr").toggleClass("highlight", this.checked);
			});

 	});
    </script>';

		return $out;
	}

	
    public function showFilterAndCheckAddButtons($addcheckuncheckall = 0, $cssclass = 'checkforaction', $calljsfunction = 0)
	{
		$out = $this->showFilterButtons();
		if ($addcheckuncheckall)
		{
			$out .= $this->showCheckAddButtons($cssclass, $calljsfunction);
		}
		return $out;
	}

	
    public function selectExpenseCategories($selected = '', $htmlname = 'fk_c_exp_tax_cat', $useempty = 0, $excludeid = array(), $target = '', $default_selected = 0, $params = array())
	{
		global $db, $conf, $langs, $user;

        $out = '';
        $sql = 'SELECT rowid, label FROM '.MAIN_DB_PREFIX.'c_exp_tax_cat WHERE active = 1';
		$sql .= ' AND entity IN (0,'.getEntity('exp_tax_cat').')';
		if (!empty($excludeid)) $sql .= ' AND rowid NOT IN ('.implode(',', $excludeid).')';
		$sql .= ' ORDER BY label';

		$resql = $db->query($sql);
		if ($resql)
		{
			$out = '<select id="select_'.$htmlname.'" name="'.$htmlname.'" class="'.$htmlname.' flat minwidth75imp">';
			if ($useempty) $out .= '<option value="0">&nbsp;</option>';

			while ($obj = $db->fetch_object($resql))
			{
				$out .= '<option '.($selected == $obj->rowid ? 'selected="selected"' : '').' value="'.$obj->rowid.'">'.$langs->trans($obj->label).'</option>';
			}
			$out .= '</select>';
			if (!empty($htmlname) && $user->admin) $out .= ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);

			if (!empty($target))
			{
				$sql = "SELECT c.id FROM ".MAIN_DB_PREFIX."c_type_fees as c WHERE c.code = 'EX_KME' AND c.active = 1";
				$resql = $db->query($sql);
				if ($resql)
				{
					if ($db->num_rows($resql) > 0)
					{
						$obj = $db->fetch_object($resql);
						$out .= '<script>
							$(function() {
								$("select[name='.$target.']").on("change", function() {
									var current_val = $(this).val();
									if (current_val == '.$obj->id.') {';
						if (!empty($default_selected) || !empty($selected)) $out .= '$("select[name='.$htmlname.']").val("'.($default_selected > 0 ? $default_selected : $selected).'");';

						$out .= '
										$("select[name='.$htmlname.']").change();
									}
								});

								$("select[name='.$htmlname.']").change(function() {

									if ($("select[name='.$target.']").val() == '.$obj->id.') {
										  
										var data = '.json_encode($params).';
										data.fk_c_exp_tax_cat = $(this).val();

										$.ajax({
											method: "POST",
											dataType: "json",
											data: data,
											url: "'.(DOL_URL_ROOT.'/expensereport/ajax/ajaxik.php').'",
										}).done(function( data, textStatus, jqXHR ) {
											console.log(data);
											if (typeof data.up != "undefined") {
												$("input[name=value_unit]").val(data.up);
												$("select[name='.$htmlname.']").attr("title", data.title);
											} else {
												$("input[name=value_unit]").val("");
												$("select[name='.$htmlname.']").attr("title", "");
											}
										});
									}
								});
							});
						</script>';
					}
				}
			}
		}
		else
		{
			dol_print_error($db);
		}

		return $out;
	}

	
    public function selectExpenseRanges($selected = '', $htmlname = 'fk_range', $useempty = 0)
	{
		global $db, $conf, $langs;

        $out = '';
		$sql = 'SELECT rowid, range_ik FROM '.MAIN_DB_PREFIX.'c_exp_tax_range';
		$sql .= ' WHERE entity = '.$conf->entity.' AND active = 1';

		$resql = $db->query($sql);
		if ($resql)
		{
			$out = '<select id="select_'.$htmlname.'" name="'.$htmlname.'" class="'.$htmlname.' flat minwidth75imp">';
			if ($useempty) $out .= '<option value="0"></option>';

			while ($obj = $db->fetch_object($resql))
			{
				$out .= '<option '.($selected == $obj->rowid ? 'selected="selected"' : '').' value="'.$obj->rowid.'">'.price($obj->range_ik, 0, $langs, 1, 0).'</option>';
			}
			$out .= '</select>';
		}
		else
		{
			dol_print_error($db);
		}

		return $out;
	}

	
    public function selectExpense($selected = '', $htmlname = 'fk_c_type_fees', $useempty = 0, $allchoice = 1, $useid = 0)
	{
		global $db, $langs;

        $out = '';
		$sql = 'SELECT id, code, label FROM '.MAIN_DB_PREFIX.'c_type_fees';
		$sql .= ' WHERE active = 1';

		$resql = $db->query($sql);
		if ($resql)
		{
			$out = '<select id="select_'.$htmlname.'" name="'.$htmlname.'" class="'.$htmlname.' flat minwidth75imp">';
			if ($useempty) $out .= '<option value="0"></option>';
			if ($allchoice) $out .= '<option value="-1">'.$langs->trans('AllExpenseReport').'</option>';

			$field = 'code';
			if ($useid) $field = 'id';

			while ($obj = $db->fetch_object($resql))
			{
				$key = $langs->trans($obj->code);
				$out .= '<option '.($selected == $obj->{$field} ? 'selected="selected"' : '').' value="'.$obj->{$field}.'">'.($key != $obj->code ? $key : $obj->label).'</option>';
			}
			$out .= '</select>';
		}
		else
		{
			dol_print_error($db);
		}

        return $out;
    }
}
