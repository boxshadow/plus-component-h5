<template>
  <div :class="$style.profile">
    <div :class="$style.header" v-if="!isWeiXin">
      我
    </div>
    <!-- 头像+昵称+简介-->
    <div :class="$style.simpleInfo" @click="changeUrl('/users/setting')">
      <Row :gutter="24" :class="$style.rowCenter">
        <!-- 头像 -->
        <Col span="6" :class="$style.colCenter">
          <img :src="avatar" :class="$style.avatar" alt="name">
        </Col>
        <!--昵称+简介-->
        <Col span="15" style="height: 56px">
          <h4 style="height: 21px;">{{ username }}</h4>
          <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ intro }}</p>
        </Col>
        <Col span="3" :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
    </div>
    <!-- 粉丝+关注 -->
    <div :class="$style.follows">
      <Row :gutter="24">
        <div :class="$style.followsContent">
          <Col span="12" style="border-right: 1px #ededed solid" @click.native="changeUrlFans(`/users/relationship/${currentUser}/followers`)">
            <p v-if="!messageCount.fans" :class="$style.contentCenter" class="followsNum">{{followed}}</p>
            <p v-else :class="$style.contentCenterHalf" class="followsNum">{{followed}}</p>
            <p v-show="messageCount.fans" :class="$style.newFollowsHalf">{{messageCount.fans}}</p>
            <p :class="$style.contentCenter">粉丝</p>
          </Col>
          <Col span="12" @click.native="changeUrl(`/users/relationship/${currentUser}/followings`)">
            <p :class="$style.contentCenter" class="followsNum">{{following}}</p>
            <p :class="$style.contentCenter">关注</p>
          </Col>
        </div>
      </Row>
    </div>
    <!-- 入口菜单 -->
    <div :class="$style.entry">
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl(`/users/feeds/${currentUser}`)">
        <Col span="3">
          <SapceIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          个人主页
        </Col>
        <Col span="5" :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/balance')">
        <Col span="3">
          <WalletIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="12" :class="$style.menuText">
          钱包
        </Col>
        <Col span="9"  :class="$style.rightIcon">
          <span :class="$style.balance">{{ showAmount(balance) }} {{ goldName }}</span><RightArrowIcon height="18" width="18" color="#ccc" />
        </Col>
      </Row>
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/users/collections')">
        <Col span="3">
          <ConnectionIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          收藏
        </Col>
        <Col span="5"  :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/users/question-answer')">
        <Col span="3">
          <ConnectionIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          我的问答
        </Col>
        <Col span="5"  :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
      <!-- <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/users/collections')">
        <Col span="3">
          <ConnectionIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          我的问答
        </Col>
        <Col span="5"  :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row> -->
    </div>
    <div :class="$style.entry">
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/users/feedback')">
        <Col span="3">
          <FeedbackIcon :height="21" :width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          意见反馈
        </Col>
        <Col span="5" :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
      <Row :gutter="24" :class="$style.entryMenu" @click.native="handleCertification">
        <Col span="3">
          <AuthIcon height="21" width="21" color="#59b6d7" />
        </Col>
        <Col span="10" :class="$style.menuText">
          认证
        </Col>
        <Col span="11" :class="$style.rightIcon">
          <span v-if="userCertification && userCertification.status == 1">已认证</span>
          <span v-else-if="userCertification && userCertification.status == 0">待审核</span>
          <span v-else>未认证</span>
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
      <Row :gutter="24" :class="$style.entryMenu" @click.native="changeUrl('/users/systemSetting')">
        <Col span="3">
          <SystemSettingIcon height="21" width="21" color="#59b6d7" />
        </Col>
        <Col span="16" :class="$style.menuText">
          设置
        </Col>
        <Col span="5" :class="$style.rightIcon">
          <RightArrowIcon height="18" width="18" color="#999" />
        </Col>
      </Row>
    </div>
    <ToolBar/>
    <mt-popup
      v-model="isShowCertification"
      position="bottom"
      style="width: 100%;"
      :class="$style.CertificationPopup"
    >
      <div>
        <Button 
          size="large" 
          :class="$style.CertificationOptions" 
          type="text" 
          :long="true"
          @click="selectCertification('user')"
        >
          个人认证
        </Button>
        <Button 
          size="large" 
          :class="$style.CertificationOptions" 
          type="text" 
          :long="true"
          @click="selectCertification('org')"
        >
          企业认证
        </Button>
        <Button  
          size="large" 
          :class="$style.CertificationOptions" 
          type="text" 
          :long="true"
          @click="hideCertificationPopup"
        >
          取消
        </Button>
      </div>
    </mt-popup>
  </div>
</template>

<script>
  import localEvent from '../stores/localStorage';
  import ToolBar from '../components/ToolBar';
  import { changeUrl, goTo } from '../utils/changeUrl';
  import FeedbackIcon from '../icons/Feedback';
  import ConnectionIcon from '../icons/Connection';
  import RankingIcon from '../icons/Ranking';
  import QuestionIcon from '../icons/Question';
  import SystemSettingIcon from '../icons/SystemSetting';
  import SapceIcon from '../icons/Space';
  import RightArrowIcon from '../icons/RightArrow';
  import WalletIcon from '../icons/Wallet';
  import { resolveImage } from '../utils/resource';
  import lodash from 'lodash';
  import { getUserInfo, getLoggedUserInfo, getUserCertification } from '../utils/user';
  import { mapState } from 'vuex';
  import { CLEANMESSAGE } from '../stores/types';
  import { showAmount } from '../utils/balance';
  import AuthIcon from '../icons/Auth';

  const defaultAvatar = resolveImage(require('../statics/images/defaultAvatarx2.png'));

  const profile = {
    components: {
      ToolBar,
      FeedbackIcon,
      ConnectionIcon,
      RankingIcon,
      QuestionIcon,
      SystemSettingIcon,
      SapceIcon,
      RightArrowIcon,
      WalletIcon,
      AuthIcon
    },
    data: () => ({
      goldName: window.TS_WEB.goldName,
      currentUser: 0, // 当前登录用户id
      userInfo: {}, // 当前登录用户信息
      userCertification: {}, //用户认证信息
      isWeiXin: TS_WEB.isWeiXin,
      isShowCertification: false,
    }),
    methods: {
      // 跳转方法，减少使用 router-link
      showAmount,
      changeUrl,
      changeUrlFans (url) {
        this.$store.dispatch(CLEANMESSAGE, cb => {
          cb('fans');
        });
        changeUrl(url);
      },
      showCertificationPopup () {
        this.isShowCertification = true;
      },
      hideCertificationPopup () {
        this.isShowCertification = false;
      },
      handleCertification () {
        let cer = this.userCertification;
        if ( (lodash.isEmpty(cer)) 
          || (!lodash.isEmpty(cer) && cer.status==2) ) {
          this.showCertificationPopup();
        } else {
          this.changeUrl(`/users/certification/show`);
        }
      },
      selectCertification (type) {
        let cer = this.userCertification;
        let state = (!lodash.isEmpty(cer) && cer.status == 2) ? 1 : 0;
        this.changeUrl(`/users/certification?type=${type}&state=${state}`);
      }
    },
    computed: {
      ...mapState({
        messageCount: state => state.messageCount.messageCount
      }),
      avatar () {
        const { avatar = defaultAvatar } = this.userInfo;
        return avatar;
      },
      username () {
        const { name = '' } = this.userInfo;
        return name;
      },
      intro () {
        const { bio = '还没有简介呢' } = this.userInfo;
        return bio;
      },
      following () {
        const { extra = {} } = this.userInfo;
        
        return (extra ? extra.followings_count : 0);
      },
      followed () {
        const { extra = {} } = this.userInfo;

        return (extra ? extra.followers_count : 0);
      },
      balance () {
        const { userInfo: { wallet: { balance = 0 } = {} } = {} } = this;
        return balance;
      }
    },
    created () {
      this.currentUser = TS_WEB.currentUserId;
      getLoggedUserInfo().then( user => {
        this.userInfo = { ...this.userInfo, ...user };
      });
      // 获取认证信息
      getUserCertification().then(data => {
        this.userCertification = data;
      });
    }
  };
  export default profile;
</script>

<style lang="scss" module>
  .profile {
    padding-bottom: 60px;
    .header {
      height: 46px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      font-weight: 400;
      background-color: #fff;
      border-bottom: 1px solid #ededed;
    }
    .avatar {
      width: 100%;
      border-radius: 50%;
    }
    .simpleInfo {
      padding-top: 16px;
      padding-bottom: 16px;
      background-color: #fff;
      border-bottom: 1px solid #ededed;
      .rowCenter {
        display: flex;
        align-items: center;
        .colCenter {
          display: flex;
          align-items: center;
        }
      }
    }
    .entry {
      margin-top: 12px;
      background-color: #fff;
      padding: 0 8px;
      .entryMenu {
        height: 50px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ededed;
        &:last-child {
          border-bottom: none;
        }
        .menuText {
          padding-left: 0!important;
        }
      }
    }
    .followsContent {
      width: 100%;
      background: #fff;
      display: flex;
      padding: 16px 0;
      font-size: 16px;
      font-weight: 400;
      .contentCenter {
        text-align: center;
      }
      .contentCenterHalf {
        width: 50%;
        display: flex;
        justify-content: flex-end;
        float: left;
      }
      .newFollowsHalf {
        display: inline-block;
        border-radius: 100px;
        color: #fff;
        background: #f00;
        min-width: 16px;
        height: 16px;
        padding: 2px 4px;
        margin-left: 8px;
        text-align: center;
        vertical-align: inherit;
        font-size: 13px;
        line-height: 1;
      }
    }
    .CertificationPopup {
      width: 100%;
      background: rgba(0, 0, 0, 0);
      .CertificationOptions {
        border-bottom: 1px solid #ededed;
        color: #333;
        border-radius: 0;
        font-size: 16px;
        &:last-child {
          margin-top: 5px;
        }
      }
    }
    .rightIcon {
      display: flex!important;
      justify-content: flex-end!important;
    }
  }
</style>
<style scope>
  .followsNum {
    color: #59b6d7;
  }
</style>
