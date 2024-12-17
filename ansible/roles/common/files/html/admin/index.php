<!doctype html>
<?php 
    require_once( '../settings.php' );
?>
<html lang='<?php echo $i18n->getLocale();?>'>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta charset="UTF-8" />
        <link href='css/loginform.css' type='text/css' rel='stylesheet' />
        <link rel="stylesheet" href="../assets/css/bootstrap.css" type='text/css' />
        <link rel="stylesheet" href='css/styles.css' type='text/css' />
        <link rel="icon" type="image/x-icon" href="../images/favicon.ico" />
    </head>
<body>
    <div id='interface' v-cloak v-on:click="closePanels">

    <div class='loginBar'>
        <div class='left'>
            <span class='arrow pointer'><a v-on:click="back"><img src='img/arrowLeft.svg'/></a></span>
        </div>
        <div class='right'>
            <span v-on:click='expandLogOff'>{{ user }}</span>
            <span v-if="isAdmin() || isTeacher()" v-on:click='expandRunLevel'><img src='img/onOff.svg'/></span>
            <a v-if="! actions" v-on:click="showLoginForm"><img src='img/loginForm.png' /></a>
        </div>
    </div>

    <div id='loader' v-if='loading'>
        <progress></progress>
    </div>

    <div id='userdetails'>
        <div class='topRight'>
            <div>
                <ul class='dropdown-right' v-if="logOffExpanded">
                    <li><a v-on:click="logOff"><span><?php echo _( 'Log off' );?></span></a></li>
                    <li><a v-on:click='doChangePassword'><?php echo _( 'Change password' );?></a></li>
                </ul>
            </div>
        </div>
        <div class='topRight' v-if='runLevelExpanded && actions'>
            <ul class='dropdown-right'>
                <li><a v-on:click='changeRunLevel0'><?php echo _( 'Turn off' );?></a></li>
                <li><a v-on:click='changeRunLevel6'><?php echo _( 'Restart' );?></a></li>
            </ul>
        </div>
    </div>

    <div class='main'>
        <div class='left'>
            <div id='actions' v-if="actions">
                <div v-bind:class="{'menu-item': true, 'menu-item-active': page == 'packagesList'}">
                    <div>
                        <a v-on:click='page = "listPackages"'>
                            <img src='img/folder.svg' title='<?php echo _( 'Stuff' );?>'/>
                        </a>
                    </div>
                </div>
                <div v-bind:class="{'menu-item':true, 'menu-item-active': page == 'usersBtns'}">
                    <div>
                        <a v-on:click='page = "usersBtns"' v-if='isTeacher()'>
                            <img src='img/student.svg' title='<?php echo _( 'Users' );?>'/>
                        </a>
                    </div>
                </div>
                <div v-bind:class="{'menu-item': true, 'menu-item-active': page == 'diskUsage'}">
                    <div>
                        <a v-on:click='page = "diskUsage"' v-if='isTeacher()'>
                            <img src='img/disk.svg' title='<?php echo _( 'Storage' );?>'/>
                        </a>
                    </div>
                </div>
                <div v-bind:class="{'menu-item': true, 'menu-item-active': page == 'syncPackages'}">
                    <div>
                        <a v-on:click='page = "syncPackages"' v-if='isTeacher()'>
                            <img src='img/sync.svg' title='<?php echo _( 'Sync' );?>'/>
                        </a>
                    </div>
                </div>
                <div v-bind:class="{'menu-item':true, 'menu-item-active': page == 'admin'}" v-if='isTeacher() || isAdmin()'>
                    <div>
                        <a v-on:click='page = "admin"'><img src='img/toolbox.svg' title='<?php echo _( 'Admin' );?>'/></a>
                    </div>
                </div>
            </div>
        </div>

        <div class='right'>
            <div class='enter-credentials login' v-if="page == 'login'">
                <login-form
                    title="<?php echo _( 'Login form' );?>"
                    username=''
                    placeholder_username='<?php echo _( 'account' );?>'
                    password=''
                    placeholder_password='<?php echo _( 'password' );?>'
                    submit='<?php echo _( 'Submit' );?>'
                    :cmd="cmd"
                    v-on:form-submit="doLogin"
                    v-on:valid-login="changePassword"
                    v-bind:error-login-txt='errorMsgLoginFalse'
                ></login-form>
            </div>

            <div v-if="page == 'usersBtns'" class='admin-blocks'>
                <div class='admin-block has-icon'>
                    <img v-on:click='page = "listUsers"' class='pointer' src='img/student.svg' title='<?php echo _( 'List users' ) ;?>'/>
                </div>
                <div class='admin-block has-icon'>
                    <img v-on:click='page = "addUser"' class='pointer' src='img/add-user.svg' title='<?php echo _( 'Add user' ) ;?>'/>
                </div>
                <div class='admin-block has-icon' title='<?php echo _( 'Add users' ) ;?>'>
                    <img v-on:click='page = "syncStudents"' class='pointer' src='img/add-users.svg' title='<?php echo _( 'Add users' ) ;?>'/>
                </div>
                <div class='admin-block has-icon'>
                    <img v-on:click='page = "resetPass"' class='pointer' src='img/key.svg' title='<?php echo _( 'Lost a password' ) ;?>'/>
                </div>
            </div>

            <div v-if='(isTeacher() || isAdmin()) && page == "admin"'>
                <admin-blocks>
                    <admin-block 
                        title='<?php echo _( 'Upload content' );?>'
                        :is-icon='true'
                        src='img/square-cross.svg'
                        >
                        <upload-content 
                            :cmd='cmd' 
                            share-txt='<?php echo _('Share');?>'
                            title-txt='<?php echo _('Title');?>'
                            update-txt='<?php echo _('Update');?>'
                            local-package-txt='<?php echo _('Local package');?>'
                            update-id-txt='<?php echo _('Search package');?>'
                            submit-txt='<?php echo _('Submit');?>'
                            nothing-found='<?php echo _('No local packages found');?>'
                            max-upload='<?php echo _f('(Max %1$s)', ini_get("upload_max_filesize"));?>'>
                        </upload-content>
                    </admin-block>
                    <admin-block 
                        :is-icon='true' 
                        title='<?php echo _('Moodle password plugin');?>'
                        src="img/moodlelogo.svg"
                        >
                        <moodle-ldap
                            :cmd='cmd'
                            submit-txt='<?php echo _('Install password plugin');?>'
                        ></moodle-ldap>
                    </admin-block>
                    <admin-block 
                        v-if="isTeacher()"
                        title='<?php echo _('Download log files');?>'
                        :is-icon="true"
                        src="img/download-log.svg"
                        >
                        <download-logs 
                            :cmd='cmd'
                            download-txt='<?php echo _('Download');?>'
                            submit-txt='<?php echo _('Generate');?>'
                        ></download-logs>
                    </admin-block>
                    <admin-block 
                        v-if='isAdmin()' 
                        title='<?php echo _( 'Backup accounts' );?>'
                        :is-icon="true"
                        src="img/download-accounts.svg"
                        >
                        <backup-useraccounts
                            :cmd='cmd'
                            download-txt='<?php echo _('Download');?>'
                            submit-txt='<?php echo _('Generate');?>'
                        ></backup-useraccounts>
                    </admin-block>
                    <admin-block
                        v-if='isAdmin()'
                        title='<?php echo _( 'Restore acounts' );?>'
                        :is-icon="true"
                        src='img/upload-accounts.svg'
                        >
                        <restore-useraccounts
                            :cmd='cmd'
                            submit-txt='<?php echo _('Upload');?>'
                        ></restore-useraccounts>
                    </admin-block>
                    <admin-block
                        v-if="hasSecondaryDisk"
                        title='<?php echo _('Secondary Content disk');?>'
                        :is-icon='true'
                        src='img/disk.svg'>
                        <secondary-disk 
                            :cmd='doCreateContentDisk' 
                            :list-packages='doListPackagesForCopy' 
                            create-btn-txt='<?php echo _( 'Create Content Disk' );?>' 
                            copy-packages-txt='<?php echo _( 'Copy packages to secondary disk' );?>'></secondary-disk>
                    </admin-block>
                    <admin-block 
                        v-if='isTeacher()'
                        title='<?php echo _('Installed versions');?>' 
                        start='start'
                        :is-icon='true'
                        src='img/info.svg'
                        >
                        <installed-versions 
                            :cmd="cmd">
                        </installed-versions>
                    </admin-block>
                </admin-blocks>
            </div>

            <div v-if='statusListPackagesForCopy'>
                <div>{{ statusListPackagesForCopy }}</div>
            </div>

            <div v-if='resultCreateContentDisk'>
                <div>{{ resultCreateContentDisk }}</div>
            </div>

            <!-- v-bind:class instead of v-if to enable to leave this page and continu syncing -->
            <sync-packages v-bind:class='{hide: page != "syncPackages"}'
                :cmd='cmd'
                :has-secondary-disk='hasSecondaryDisk'
                :lang="getTranslationLanguageCode()"
                warning-txt="<?php echo _( 'Bandwidth rates apply when connecting to an online resource.' ); ?>"
                looking-for-source-txt="<?php echo _('Looking for sources');?>"
                elimugo-txt="<?php echo _('ElimuGo app');?>" 
                second-disk-txt="<?php echo _( 'Secondary Disk' );?>"
                online-txt="<?php echo _( 'ElimuPi Online' );?>"
                installed-txt="<?php echo _( 'Installed :' );?>"
                new-txt="<?php echo _( 'New :' );?>"
                refresh="<?php echo _( 'Refresh' );?>"
                updates-txt="<?php echo _( 'Updates :' );?>"
                copied-txt="<?php echo _( 'Copied :' );?>"
                verified-txt="<?php echo _( 'Verified :' );?>"
                installed-txt="<?php echo _( 'Installing :' );?>"
                button-txt="<?php echo _( 'Fetch all' );?>"
                >
            </sync-packages>

            <div v-if='listPackagesForCopy' id='selectedPackagesForCopy'>
                <div v-if=' ! resultCopyPackagesToDisk'>
                    <div v-if='listPackagesForCopy.updates.length > 0'>
                        Updates : 
                        <div v-for='package in listPackagesForCopy.updates'>
                            <div>
                                <label class="switch">
                                    <input type='checkbox' v-bind:name="package.uniqueId" checked />
                                    <span class="slider round"></span>
                                </label>
                                {{ package.description }}
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        No updates for packages
                    </div>
                    <div v-if='listPackagesForCopy.new.length > 0'>
                        New :
                        <div v-for='package in listPackagesForCopy.new'>
                            <div>
                                <label class="switch">
                                    <input type='checkbox' v-bind:name="package.uniqueId" checked />
                                    <span class="slider round"></span>
                                </label>
                                {{ package.description }}
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        No new packages
                    </div>
                </div>
                <div v-else>{{ resultCopyPackagesToDisk }}</div>
                <div v-if='listPackagesForCopy.new.length > 0 || listPackagesForCopy.updates.length > 0'>
                    <a v-on:click='doCopyPackagesToSecondaryDisk'><?php echo _( 'Copy packages' );?></a>
                </div>
                <br>
            </div>

            <div class='scrollable'>
                <list-users v-if='page == "listUsers"'
                    :cmd='cmd'
                    title-txt="<?php echo _( 'Users' );?>"
                    search-txt="<?php echo _( 'search' );?>"
                    v-on:view-user="viewUser"
                    ></list-users>

                <view-user v-if='page == "viewUser"'
                    :cmd='cmd'
                    title-txt="<?php echo _('View account');?>"
                    :user='selectedUser'
                    v-on:reset-password="resetPassword"
                >
                </view-user>

                <change-password 
                    v-if='page == "changePassword"'
                    :is-valid-password='isValidPassword'
                    :cmd='cmd'
                    v-on:password-change='passwordUpdated'
                    new-password-txt='<?php echo _( 'new password' );?>'
                    password-again='<?php echo _( 'new password again' );?>'
                    submit-txt='<?php echo _( 'Submit' );?>'>
                </change-password>

                <reset-password 
                    v-if='page == "resetPass"' 
                    :cmd='cmd'
                    :is-valid-password='isValidPassword'
                    :is-admin='isAdmin()'
                    :user='selectedUser'
                    title-txt='<?php echo _( 'New password' );?>'
                    new-password-txt='<?php echo _( 'new password' );?>'
                    new-password-again-txt='<?php echo _( 'new password again' );?>'
                    account-txt='<?php echo _( 'account' );?>'
                    submit-txt='<?php echo _( 'Submit' );?>'>
                </reset-password>

                <!-- Upload students -->
                <sync-students 
                    v-if='page == "syncStudents"'
                    :cmd='cmd'
                    :is-valid-password='isValidPassword'
                    title-txt='<?php echo _( 'Upload students' );?>'
                    remove-txt='<?php echo _( 'Remove students not in the file :' );?>'
                    initial-password-txt='<?php echo _( "Initial password" );?>'
                    submit-txt='<?php echo _( 'Submit' );?>'
                    :start-adding='msgStartAddingStudents'
                    added-txt='<?php echo _('Added:');?>'
                    removed-txt='<?php echo _('Removed:');?>'
                    :nothing-added='msgNothingAdded'>
                </sync-students>

                <!-- Add user -->
                <add-user v-if='page == "addUser"'
                    :is-valid-password='isValidPassword'
                    :cmd='cmd'
                    title-txt='<?php echo _( 'Add a user' );?>'
                    removed-txt='<?php echo _( 'Removed :' );?>'
                    added-txt='<?php echo _( 'Added:' );?>'
                    name-txt='<?php echo _( "name");?>'
                    enter-password-txt='<?php echo _( "enter password");?>'
                    enter-password-again-txt='<?php echo _( "enter password again");?>'
                    type-student-txt='<?php echo _( 'Student' );?>'
                    type-teacher-txt='<?php echo _( 'Teacher' );?>'
                    submit-txt='<?php echo _( 'Submit' );?>'
                ></add-user>

                <disk-usage v-if="page == 'diskUsage'" class=''
                    title-txt='<?php echo _( 'Disk usage :' ); ?>'
                    text-txt='<?php echo _( 'Internal storage :' );?>'
                    total-used-txt='<?php echo _( 'Total used of' );?>'
                    mount-txt='<?php echo _( 'Mount' );?>'
                    eject-txt='<?php echo _( 'Eject' );?>'
                    :is-admin='isAdmin()'
                    :cmd='cmd'
                    v-on:has-secondary-disk='disksChanged'
                >
                </disk-usage>

                <div v-if='page == "listPackages"'>
                    <div id='packagesList' class='scroll2'>
                        <div class='search'>
                            <form action='javascript:void(0)' v-on:submit='searchPackages'>
                                <input v-on:keyup='searchPackages' type='text' name='search' placeholder='<?php echo _( 'search' );?>'></input> 
                                <span v-if='installAgain.length > 0'><a class='pointer' v-on:click.stop="installPackagesAgain"><?php echo ( 'Install again' );?></a>: {{ installAgain.length }}</span>
                            </form>
                        </div>
                        <div class='list'></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        var errorMsgLoginFalse = '<?php echo _( "Login not correct. Try again." ); ?>',
            errorMsgPasswordTooShort = '<?php echo _( 'Password is too short.' );?>',
            msgStartAddingStudents = '<?php  echo _('Start adding students.');?>',
            msgStartRemovingStudents = '<?php echo _( 'Start syncing students.' );?>',
            msgNothingAdded = '<?php echo _( 'Nothing added.' );?>',
            msgNoRemovalDone = '<?php echo _( 'No removal done.' );?>',
            msgDoneUpdating = '<?php echo _( 'Done updating.' );?>',
            msgFetchingPackagesInfo = '<?php echo _( 'Fetching packages info.' );?>',
            msgCopyingPackages = '<?php echo _( 'Copying packages.' );?>',
            msgVeryfyingPackages = '<?php echo _( 'Verifying packages.' );?>',
            msgInstallingPackages = '<?php echo _('Installing packages');?>',
            msgToday = '<?php echo _('today');?>',
            msgOneDayAgo = '<?php echo _( 'day ago' );?>',
            msgDaysAgo = '<?php echo _( 'days ago' );?>',
            msgOneYearAgo = '<?php echo _( 'year ago' );?>',
            msgYearsAgo = '<?php echo _( 'years ago' );?>',
            msgOneMonthAgo = '<?php echo _( 'month ago' );?>',
            msgMonthsAgo = '<?php echo _( 'months ago' );?>',
            msgOneWeekAgo = '<?php echo _( 'week ago' );?>',
            msgWeeksAgo = '<?php echo _( 'weeks ago' );?>',
            msgViewable = '<?php echo _( 'Read' );?>', 
            msgDownloadable = '<?php echo _( 'Download' );?>',
            msgInstallable = '<?php echo _( 'Install again' );?>';
            msgNothingFound = '<?php echo _( 'Nothing found.' );?>';

    </script>

    <script src='js/vue.min.js'></script>

    <script src='js/login-form.js'></script>

    <script src='js/admin-blocks.js'></script>

    <script src='js/admin-block.js'></script>

    <script src='js/upload-content.js'></script>

    <script src='js/download-logs.js'></script>

    <script src='js/backup-useraccounts.js'></script>

    <script src='js/restore-useraccounts.js'></script>

    <script src='js/installed-versions.js'></script>

    <script src='js/secondary-disk.js'></script>

    <script src='js/moodle-ldap.js'></script>

    <script src='js/change-password.js'></script>

    <script src='js/add-user.js'></script>

    <script src='js/reset-password.js'></script>

    <script src='js/disk-usage.js'></script>

    <script src='js/sync-students.js'></script>

    <script src='js/list-users.js'></script>

    <script src='js/view-user.js'></script>

    <script src='js/sync-packages.js'></script>

    <script src='js/adminPanel.js'></script>
    
</div>
</body>
</html>