<template>
  <div>
    <div id="spinner" v-show="showSpinner">
      <div id="spinner-parent">
        <div class="spinner-double-bounce-bounce2" />
        <div class="spinner-double-bounce-bounce1" />
      </div>
    </div>
    <header class="commonHeader" id="feed-header" v-if="!isWeixin">
      <Row :gutter="24">
        <Col span="5" style="display: flex; justify-content: flex-start">
          <BackIcon @click.native="goBack()" height="21" width="21" color="#999" />
        </Col>
        <Col span="14" class="title-col">
          <div>
            <span :class="$style.newsTitle" style="font-size: 18px; padding: 0 5px">{{ subject }}</span>
          </div>
        </Col>
      </Row>
    </header>
    <div :class="{headerPadding: !isWeixin}">
        <section :class="$style.answerUser">
          <Row :gutter="24" v-if="answer.anonymity">
            <Col span="5"> 
              <img class="component-avatar" v-lazy="avatar" alt="">
            </Col>
            <Col span="19">
              <h3>{{ name }}</h3>
            </Col>
          </Row>
          <Row :gutter="24" v-else>
            <Col span="5"> 
              <img class="component-avatar" v-lazy="avatar" alt="">
            </Col>
            <Col span="13">
              <h3>{{ name.name }}</h3>
              <p :class="$style.bio">{{ name.bio }}</p>
            </Col>
            <Col span="6" class="header-end-col">
              <Button v-if="!following" class="followAboutButton" type="ghost" @click.native="doFollow">
                <PlusIcon height="16" width="16" color="#ccc" />
                关注
              </Button>
              <Button class="followAboutButton" v-else-if="following && !follower" type="ghost" @click.native="doUnFollow">
                <RightIcon height="16" width="16" color="#ccc" />
                已关注
              </Button>
              <Button class="followAboutButton" v-else type="ghost" @click.native="doUnFollow">
                <EachFollowingIcon height="16" width="16" color="#ccc" />
                相互关注
              </Button>
            </Col>
          </Row>
        </section>
        <div class="feed-container">
          <div class="feed-container-content feed-background-color markdown-body">
            <div>
              <section 
                class="feedContainerContentTextNoPadding"
                v-html="`${markedBody}`"
              >
              </section>
            </div>
          </div>
        </div>
        <RewardEntry class="feed-background-color" v-if="answer.id" component="question-answers" :rewardableId="answer.id" api-method="rewarders" :source="answer" />
        <div class="feed-container-comments feed-background-color">
         <!--input box start-->
          <ul :class="$style.comment" v-if="commentFeed" ref="commentFeedInput">
            <li>
              <Input 
                type="textarea" 
                ref="commentInput"
                class="commentInput"
                :autosize="{ minRows: 1, maxRows: 4 }" 
                :minlength='1' 
                :maxlength='255'
                :autofocus="true"
                v-model="commentBody"
                :placeholder="placeholder"
                v-childfocus
              />
            </li>
            <li :class="$style.commentOperations">
              <p :class="$style.commentOperation" v-show="commentCount > 100">
                <span :class="{ inputFull: commentCount > 100 }">{{ commentCount }}</span>/255
              </p>
              <Button :class="$style.commentOperation" type="text" class="sendButton" size="small" @click.native="handleCommentInput(false)">取消</Button>
              <Button 
                :class="$style.commentOperation" 
                type="primary" 
                class="sendButton" 
                :disabled="!commentCount" 
                size="small" 
                @click.native="sendComment()"
              >
                发送
              </Button>
            </li>
          </ul>
          <!-- end input box -->
          <div class="noComment" v-if="!answer.comments_count">
            <img :src="defaultImage" />
          </div>
          <div class="comments" v-else>
            <Row :gutter="24" class="comments_count" style="height: 45px; display: -webkit-flex; display: flex; -webkit-align-items: center; align-items: center;">
              <Col span="24">
                  <span class="comments_counter">
                    {{answer.comments_count}}人评论
                  </span>
              </Col>
            </Row>
            <mt-loadmore 
              :bottom-method="loadBottom"
              :bottom-all-loaded="bottomAllLoaded"
              :top-method="loadNew"
              ref="loadmore"
              @bottom-status-change="bottomStatusChange"
              :bottomDistance="70"
            >
              <div class="comments-content">
                <section v-for="(comment, index) in formateComments" :key="comment.id" >
                  <Row :gutter="24" :class="$style.perComment">
                    <Col span="4">
                      <div class="grid-content bg-purple">
                        <img class="component-avatar" @click="changeUrl(`/users/feeds/${comment.user.user_id}`)" :src="comment.user.avatar" />
                      </div>
                    </Col>
                    <Col span="20">
                      <div class="grid-content bg-purple">
                        <Row style="padding-bottom: 5px">
                          <Col span="17">
                            <router-link :class="$style.profileLink" :to="{ path: `/users/feeds/${comment.user.user_id}` }">{{ comment.user.name }}</router-link>
                          </Col>
                          <Col span="7" style="display: flex; justify-content: flex-end;">
                            <timeago style="color: #ccc; font-size: 12px;" :since="timers(comment.created_at, 8, false)" locale="zh-CN" :auto-update="60"></timeago>
                          </Col>
                        </Row>
                        <Row>
                          <Col span="24">
                            <div style="color: #ccc;">
                              <span v-if="comment.reply_user">回复 </span>
                              <router-link :class="$style.profileLink" :to="{ path: `/profile/${comment.reply_user}` }">{{ comment.replyToUser.name }} </router-link>
                              <span
                                v-if="comment.user_id  != currentUser"
                                @click.stop="focusInput(comment.user_id, index)"
                                :class="$style.commentContent"
                              > 
                               {{ comment.body }}
                              </span>
                              <span
                                v-if="comment.user_id == currentUser"
                                @click.stop="showComfirm(comment.id, answer.id, index)"
                                :class="$style.commentContent"
                              > 
                               {{ comment.body }}
                              </span>
                            </div>
                          </Col>
                        </Row>
                      </div>
                    </Col>
                  </Row>
                  <ul :class="$style.comment" v-if="index === commentIndex" ref="commentFeedInput">
                    <li>
                      <Input 
                        type="textarea" 
                        ref="commentUserInput"
                        class="commentInput"
                        :autosize="{ minRows: 1, maxRows: 4 }" 
                        :minlength='1' 
                        :maxlength='255'
                        :autofocus="true"
                        v-model="commentBody"
                        :placeholder="placeholder"
                      />
                    </li>
                    <li :class="$style.commentOperations">
                      <p :class="$style.commentOperation" v-show="commentCount > 100">
                        <span :class="{ inputFull: commentCount > 100 }">{{ commentCount }}</span>/255
                      </p>
                      <Button :class="$style.commentOperation" type="text" class="sendButton" size="small" @click.native="handleCommentInput(false)">取消</Button>
                      <Button 
                        :class="$style.commentOperation" 
                        type="primary" 
                        class="sendButton" 
                        :disabled="!commentCount" 
                        size="small" 
                        @click.native="sendComment()"
                      >
                        发送
                      </Button>
                    </li>
                  </ul>
                </section>
              </div>
            <div slot="bottom" style="display: flex; justify-content: center; align-items: center; padding: 10px 0;">
              <span v-show="bottomAllLoaded && bottomStatus !== 'loading' && comments.length <= limit">没有更多了</span>
              <span v-show="bottomStatus === 'loading'">加载中...</span>
              <span v-show="bottomStatus === 'pull' && !bottomAllLoaded">上拉加载更多</span>
              <span v-show="bottomStatus === 'drop'">释放加载更多</span>
            </div>
          </mt-loadmore>
        </div>
      </div>
    </div>
    <div id="feed-footer" class="feed-container-tool-operation feed-background-color">
      <Row :gutter="24" style="display: flex; justify-content: center; align-items: center; height: 100%;">
        <Col span="6" class="operation">
          <div v-if="!answer.liked" @click="handleDiggFeed(answer.id)">
            <UnDiggIcon height="20" width="20" color="#999" />
            <i>喜欢</i>
          </div>
          <div v-else @click="handleUnDiggFeed(answer.id)">
            <DiggIcon height="20" width="20" color="#f4504d" />
            <i class="did">喜欢</i>
          </div>
        </Col>
        <Col span="6" class="operation" @click.native="focusInput()">
          <div>
            <CommentIcon height="20" width="20" color="#999" />
            <i>评论</i>
          </div>
        </Col>
        <Col span="6" class="operation">
          <div>
            <ShareIcon height="20" width="20" color="#999" />
            <i>分享</i>
          </div>
        </Col>
        <Col span="6" class="operation">
          <div @click="showPopup">
            <MoreIcon height="20" width="20" color="#999" />
            <i>更多</i>
          </div>
        </Col>
      </Row>
    </div>
    <mt-popup
      v-model="isShowPopup"
      position="bottom"
      style="width: 100%;"
      :class="$style.popup"
      v-if="questionUser === user_id"
    >
      <div>
        <Button 
          @click="deleteAnswer" 
          size="large" 
          :class="[$style.deleteQuestion, $style.popupButton]" 
          type="text" :long="true"
          v-if="userId === user_id"
        >
          删除
        </Button>
        <Button 
          @click="answer.adoption ? adoptionAnswer : ''" 
          size="large" 
          :class="[$style.askForexCellent, $style.popupButton]" 
          type="text" :long="true"
        >
          {{ answer.adoption ? '已采纳' : '采纳答案'}}
        </Button>
        <Button
          @click.native="handleCollection(answer.id)" 
          size="large" 
          :class="[$style.popupButton]" 
          type="text" 
          :long="true"
          v-if="!answer.collected"
        >
          收藏
        </Button>
        <Button
          v-else
          @click.native="handleUnCollection(answer.id)" 
          size="large" 
          :class="$style.popupButton" 
          type="text" 
          :long="true"
        >
          取消收藏
        </Button>
        <Button
          v-if="userId === user_id"
          @click="editorAnswer" 
          size="large" 
          :class="[$style.deleteQuestion, $style.popupButton]" 
          type="text" 
          :long="true"
        >
          编辑
        </Button>
        <Button 
          @click="hidePopup" 
          size="large" 
          :class="$style.popupButton" 
          type="text" 
          :long="true"
        >
          取消
        </Button>
      </div>
    </mt-popup>
    <mt-popup
      v-model="isShowPopup"
      position="bottom"
      style="width: 100%;"
      :class="$style.popup"
      v-else
    >
      <div>
        <Button 
          @click.native="deleteAnswer" 
          size="large" 
          :class="[$style.deleteQuestion, $style.popupButton]" 
          type="text" :long="true"
          v-if="userId === user_id"
        >
          删除
        </Button>
        <Button
          @click.native="handleCollection(answer.id)" 
          size="large" 
          :class="[$style.popupButton]" 
          type="text" 
          :long="true"
          v-if="!answer.collected"
        >
          收藏
        </Button>
        <Button
          v-else
          @click.native="handleUnCollection(answer.id)" 
          size="large" 
          :class="$style.popupButton" 
          type="text" 
          :long="true"
        >
          取消收藏
        </Button>
        <Button
          v-if="userId === user_id"
          @click.native="editorAnswer" 
          size="large" 
          :class="$style.popupButton" 
          type="text" 
          :long="true"
        >
          编辑
        </Button>
        <Button 
          @click.native="hidePopup" 
          size="large" 
          :class="$style.popupButton" 
          type="text" 
          :long="true"
        >
          取消
        </Button>
      </div>
    </mt-popup>
  </div>
  <!-- </transition> -->
</template>
<script>
  import { createAPI, addAccessToken, createOldAPI } from '../../utils/request';
  import errorCodes from '../../stores/errorCodes';
  import localEvent from '../../stores/localStorage';
  import { getUserInfo } from '../../utils/user';
  import { NOTICE, CONFIRM } from '../../stores/types';
  import { friendNum } from '../../utils/friendNum';
  import Comfirm from '../../utils/Comfirm';
  import formateFeedComments from '../../utils/formateFeedComments';
  import UnFollowingIcon from '../../icons/UnFollowing';
  import FollowingIcon from '../../icons/Following';
  import EachFollowingIcon from '../../icons/EachFollowing';
  import DiggIcon from '../../icons/Digg';
  import UnDiggIcon from '../../icons/UnDigg';
  import CommentIcon from '../../icons/Comment';
  import ShareIcon from '../../icons/Share';
  import ConnectionIcon from '../../icons/Connection';
  import BackIcon from '../../icons/Back';
  import timers from '../../utils/timer';
  import lodash from 'lodash';
  import { resolveImage } from '../../utils/resource';
  import { changeUrl, goTo } from '../../utils/changeUrl';
  import getLocalTime from '../../utils/getLocalTime';
  import markdownIt from 'markdown-it';
  import plusImageSyntax from 'markdown-it-plus-image';
  import hljs from 'highlight.js';
  import RewardEntry from '../../components/RewardEntry';
  import { unFollowingUser, followingUser } from '../../utils/user';
  import PlusIcon from '../../icons/Plus';
  import RightIcon from '../../icons/Right';
  import EachFollowIcon from '../../icons/EachFollowing';
  import MoreIcon from '../../icons/More';

  const defaultAvatar = resolveImage(require('../../statics/images/defaultAvatarx2.png'))
  // markdown 解析
  const md = markdownIt({
    breaks: true,
    html: false,
    highlight: function (str, lang) {
      if (lang && hljs.getLanguage(lang)) {
        try {
          return hljs.highlight(lang, str).value;
        } catch (__) {}
      }
   
      try {
        return hljs.highlightAuto(str).value;
      } catch (__) {}
   
      return ''; // use external default escaping 
    }
  }).use(plusImageSyntax, `/api/v2/files/`);
  // 引入样式库
  import "github-markdown-css";
  import 'highlight.js/styles/github.css';
  
  const defaultImage = resolveImage(require('../../statics/images/defaultNothingx2.png'));
  const AnswerDetail = {
    components: {
      MoreIcon,
      EachFollowIcon,
      PlusIcon,
      RightIcon,
      Comfirm,
      UnFollowingIcon,
      FollowingIcon,
      EachFollowingIcon,
      DiggIcon,
      UnDiggIcon,
      CommentIcon,
      ShareIcon,
      ConnectionIcon,
      BackIcon,
      RewardEntry
    },
    data: () => ({
      defaultAvatar,
      isShowPopup: false,
      isWeixin: window.TS_WEB.isWeiXin,
      scroll: 0,
      answer: {
        reward: {
          amount: 0,
          count: 0
        }
      },
      comments: [],
      defaultImage,
      currentUser: window.TS_WEB.currentUserId,
      // 上拉加载更多相关
      bottomAllLoaded: true,
      max_comment_id: 0,
      bottomStatus: '',
      showSpinner: true,
      // 输入框有关
      commentFeed: false,
      commentToUserId: 0,
      placeholder: '',
      loading: false,
      commentedUser: {},
      commentIndex: -1,
      commentBody: '',
      reward: {},
      limit: 20,
      user_id: 0
    }),
    computed: {

      answerBody () {
        const { body = '' } = this.answer;
        return body;
      },
      markedBody(){
        return (md.render(this.answerBody));
      },
      commentCount () {
        return this.commentBody.length;
      },
      newsTimer () {
        return this.timers(this.answer.created_at, 8, false);
      },
      formateComments () {
        let formated = formateFeedComments(this.comments);
        this.max_id = formated.max_id;
        return formated.comments;
      },
      subject () {
        const { question: { subject = '' } = {} } = this.answer;
        return subject;
      },
      questionUser () {
        const { question: { user_id = 0 } = {} } = this.answer;
        return user_id;
      },
      userId () {
        const { answer: { user_id = 0 } = {} } = this;
        return user_id;
      },
      avatar () {
        const { defaultAvatar, answer: { anonymity = 0, user: { avatar = ''} = {} } = {} } = this;
        if (anonymity) return defaultAvatar;
        return avatar || defaultAvatar;
      },
      name () {
        const { answer: { anonymity = 0, user: { name = '', bio = ''} = {} } = {} } = this;
        if (anonymity) return '匿名用户';
        return {
          name,
          bio
        }
      },
      // 是否关注答主
      following () {
        const { answer: { user: { follower = false } = {} } = {} } = this;
        return follower;
      },

      // 是否被答主关注
      follower () {
        const { answer: { user: { following = false } = {} } = {} } = this;
        return following;
      }
    },
    methods: {
      changeUrl,
      timers,
      unFollowingUser,
      followingUser,
      /**
       * 编辑答案
       * @return {[type]} [description]
       */
      editorAnswer () {

      },
      /**
       * 删除答案
       * @return {[type]} [description]
       */
      deleteAnswer () {
        
        let id = this.answer.question.id;
        let aid = this.answer.id;
        addAccessToken().delete(
          createAPI(`question-answers/${aid}`),
          {
            validateStatus: status => status === 204
          }
        )
        .then( () => {
          this.$Message.success('删除成功');
          this.$router.push({ name: 'questionDetail', params: { question_id: id } });
        })
        .catch(({ response: { data } }) => {
          this.$Message.error(this.$MessageBundle(data).getMessage());
        });
      },
      hidePopup () {
        this.isShowPopup = false;
      },
      showPopup () {
        this.isShowPopup = true;
      },
      doFollow() {
        const { answer: { user } } = this;
        let newUser = user;
        followingUser(this.userId)
        .then(status => {
          newUser.follower = true;
          this.answer.user = {
            ...this.answer.user,
            ...newUser
          }
        });
      },

      doUnFollow () {
        const { answer: { user } } = this;
        let newUser = user;
        unFollowingUser(this.userId)
        .then( status => {
          newUser.follower = false;
          this.answer.user = {
            ...this.answer.user,
            ...newUser
          }
        });
      },
      // 检测底部loading的状态变化
      bottomStatusChange (status) {
        this.bottomStatus = status;
      },
      removeByValue (arr, value) {
        for(let i=0; i<arr.length; i++) {
          if(arr[i] == value) {
            arr.splice(i, 1);
            break;
          }
        }
      },
      handleCollection (id) {
        addAccessToken().post(createAPI(`user/question-answer/collections/${id}`), {}, {
          validateStatus: status => status === 201
        })
        .then(response => {
          let data = response.data;
          this.answer.collected = true;
        })
      },
      handleUnCollection (id) {
        addAccessToken().delete(createAPI(`user/question-answer/collections/${id}`), {}, {
          validateStatus: status => status === 204
        })
        .then(response => {
          this.answer.collected = false;
        })
      },
      handleDiggFeed (id) {
        addAccessToken().post(createAPI(`question-answers/${id}/likes`), {}, {
          validateStatus: status => status === 201
        })
        .then( () => {
          this.answer.liked = true;
        })
      },
      handleUnDiggFeed (id) {
        addAccessToken().delete(createAPI(`question-answers/${id}/likes`), {}, {
          validateStatus: status => status === 204
        })
        .then(response => {
          this.answer.liked = false;
        })
      },
      loadNew () {
        const { limit } = this;

        addAccessToken().get(
          createAPI(`question-answers/${this.answer.id}/comments?limit=${limit}`),
          {},
          {
            validateStatus: status => status === 200
          }
        )
        .then( ({ data = {} }) => {
          this.comments = lodash.uniqBy([
            ...data,
            ...this.comments
          ], 'id');
          // 若数据已全部获取完毕
          this.$refs.loadmore.onTopLoaded();
        })
      },
      loadBottom() {
        const { limit } = this;
        if (this.bottomAllLoaded) {
          this.$refs.loadmore.onBottomLoaded();
          return;
        }
        let max_id = this.max_id;
        this.bottomStatus = 'loading';
        addAccessToken().get(
          createAPI(`question-answers/${this.answer.id}/comments?after=${max_id}&limit=${limit}`),
          {},
          {
            validateStatus: status => status === 200
          }
        )
        .then( ({ data = {} }) => {
          if(comments.length === limit) {
            this.bottomAllLoaded = false;
          } else {
            this.bottomAllLoaded = true;
          }
          this.comments = [
            ...this.comments,
            ...data
          ]
          // 若数据已全部获取完毕
          this.bottomStatus = '';
          this.$refs.loadmore.onBottomLoaded();
        })
      },
      /**
       * [handleCommentInput reset comment input]
       * @return {[type]} [description]
       */
      handleCommentInput () {
        this.commentFeed = false;
        this.commentToUserId = 0;
        this.placeholder = '';
        this.commentIndex = -1;
        this.commentedUser = {};
        this.commentBody = '';
      },
      /**
       * 发表评论
       * @return {[type]} [description]
       */
      sendComment () {
        if(!this.commentBody.length && this.loading) return;
        this.loading = true;
        addAccessToken().post(createAPI(`question-answers/${this.answer.id}/comments`), {
            body: this.commentBody,
            reply_user: this.commentToUserId
          },
          {
            validateStatus: status => status === 201
          }
        )
        .then( ({ data = {}}) => {
          let newComment = { ...data.comment };
          // current logged user
          
          let item = this.$storeLocal.get(window.TS_WEB.currentUserId);

            // don't find local db user
          if(item === undefined) {
            getUserInfo(window.TS_WEB.currentUserId).then( user => {
              newComment.user = { ...user };
              // commented user
              if(this.commentToUserId) {
                newComment.reply_to_user = { ...this.commentedUser };
                this.comments.unshift(newComment);
              } else {
                this.comments.unshift(newComment);
              }
            });
          } else { // find local db user
            newComment.user = { ...item };

            // commented user
            if(this.commentToUserId) {
              newComment.reply_to_user = { ...this.commentedUser };
              this.comments.unshift(newComment);
            } else {
              this.comments.unshift(newComment);
            }
          }
          
          // 本地数据更新
          this.answer.comments_count += 1;
          this.$store.dispatch(NOTICE, cb => {
            cb({
              text: '已发送',
              time: 1500,
              status: true
            });
          });
          // reset comment input
          this.handleCommentInput();

          this.loading = false;
        });
      },
      /**
       * 打开评论输入框
       * @param  {Number} comment_to_uid [回复某个用户ID]
       * @param  {Number} index          [被回复的评论在数组中的index]
       * @return {[type]}                [description]
       */
      focusInput (comment_to_uid = 0, index = -1) {
        // 对评论进行评论
        let reply_to_user_id = this.commentToUserId = comment_to_uid;
        if(reply_to_user_id) {
          this.commentIndex = index;
          this.commentToUserId = reply_to_user_id;
          
          let item = this.$storeLocal.get(reply_to_user_id);
          if(item === undefined) {
            getUserInfo( reply_to_user_id ).then( user => {
              this.placeholder = `回复： ${user.name}`;
              this.commentedUser = { ...user };
            })
          } else {
            this.placeholder = `回复: ${item.name}`;
            this.commentedUser = { ...item };
          }
        } else {
          this.placeholder = '随便说说';
          this.commentFeed = true;
        }
      },
      // 新版删除确认提示
      showComfirm (commentId, id, index) {
        this.$store.dispatch(CONFIRM, cb => {
          cb({
            show: true,
            confirmContent: '删除动态',
            data: {
              comment_id: commentId,
              id: id,
              index: index
            },
            confirm: this.deleteComment
          })
        })
      },
      deleteComment (close, data) {
        addAccessToken().delete(createAPI(`question-answers/${data.id}/comments/${data.comment_id}`), {}, {
          validateStatus: status => status === 204
        })
        .then(response => {
          let newComments = this.comments;
          newComments.splice(data.index,1);
          this.comments = newComments;
          this.answer.comments_count -- ;

          this.$Message.success({
            content: '已删除'
          })
        })
      },
      friendNum,
      goBack() {
        if(window.history.length < 2) {
          this.$router.push('/discover');
          return;
        }
        this.$router.back();
      },
      menu() {
        let header = document.getElementById('feed-header');
        let footer = document.getElementById('feed-footer');
        if(header || footer) {
          if(this.scroll > 55) {
            if(this.scroll > window.pageYOffset) {
              if(header) header.style.top = 0;
              if(footer) footer.style.bottom = 0;
            } else {
              if(header) header.style.top = '-55px';
              if(footer) footer.style.bottom = '-55px';
            }
          }
          this.scroll = window.pageYOffset;
        }
      }
    },
    beforeMount () {
      const { limit } = this;
      const { user_id = 0 } = this.$storeLocal.get('UserLoginInfo');
      this.user_id = user_id;
      let answer_id = parseInt(this.$route.params.answer_id) || 0;
      if ( !answer_id ) {
        this.$store.dispatch(NOTICE, cb => {
          cb({
            text: '发生一些错误',
            time: 1500,
            status: false
          });
        });
        setTimeout(() => {
          goTo(-1);
        }, 1500);
        return;
      }
        // 获取动态详情
        addAccessToken().get(
          createAPI(`question-answers/${answer_id}`),
          {},
          {
            validateStatus: status => status === 200
          }
        )
        .then(({ data = {}}) => {
          data.reward = {
            amount: data.rewards_amount,
            count: data.rewarder_count
          }
          this.answer = { ... data };

          // 获取评论 前20条
          addAccessToken().get(
            createAPI(`question-answers/${answer_id}/comments`),
            {
              validateStatus: status => status === 200
            }
          )
          .then(({ data = {} }) => {
            // 格式化评论列表
            this.comments = data;

            if(this.comments.length === limit) {

              this.bottomAllLoaded = false;
            } else {
              this.bottomAllLoaded = true;
            }

            if(this.$refs.loadmore)
              this.$refs.loadmore.onTopLoaded();
            this.showSpinner = false;
          });
        })
        .catch(({response: { data, status } }) => {
          if( status === 404 ) {
            this.$Message.error('该回答已经找不到了...');
            this.$router.push('/questions');
          }
        });
    },
    mounted () {
      window.addEventListener('scroll', this.menu);
    }
  }; 

  export default AnswerDetail;
</script>
<style>
  .feedContainerContentTextNoPadding pre, feedContainerContentText pre {
    overflow-x: scroll; 
  }
</style>
<style lang="less" scoped>
  .mint-loadmore-content-parent-no-trans .mint-loadmore-content {
    transform: inherit!important;
  }
  .feed-container {
    background-color: #f4f5f5;
  }
  #feed-footer {
    transition: bottom .2s;
  }
  .headerNoPadding {
    padding-top: 0;
  }
  .headerPadding {
    padding-top: 46px;
  }
  .commonHeader{
    position: fixed!important;
    top: 0;
    left: 0;
    right: 0;
    transition: top .2s;
    div {
      height: 100%;
      display: flex;
      align-items: center;
    }
    a {
      display: flex;
      height: 100%;
      justify-content: center;
      align-items: center;
      padding: 5px 0;
      .avatar-content {
        height: 100%;
        display: flex;
        border-radius: 50%;
        overflow: hidden;
        align-items: center;
        img.avatar {
          object-fit: cover;
          color: #333;
          width: auto;
          height: 100%;
        }
      }
    }
  } 
  .feed-container-content { 
    padding-top: 12px;
    .feed-container-content-images {
      div {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 5px;
        width: 100%;
        img[lazy='loading'] 
        {
          width: 100%
        }
        img[lazy='loaded'] 
        {
          width: 100%;
        }
      }
    }
    .feedContainerContentText {
      padding: 20px 12px;
      p {
        margin: 8px 0;
      }
    }
    .feedContainerContentTextNoPadding {
      padding: 0 12px;
      word-break: break-all;
      p {
        margin: 8px 0;
      }
    }
  }
  .digg-digg-list {
    position: relative;
    height: 30px;
    img {
      position: absolute;
      height: 100%;
      border: 2px #fff solid;
      border-radius: 50%;
      &[lazy="loading"] {
        height: 100%;
        width: auto;
      }
    }
  }
  .detail-data {
    p {
      font-size: 12px;
      color: #ccc;
      text-align: right;
    }
  }
  .feed-container-tool-operation {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 55px;
    z-index: 6;
    .operation {
      div { 
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        svg {
          width: 100%;
        }
        i {
          font-style: normal;
          color: #999;
        }
        i.did {
          color: #f4504d;
        }
      }
    }
  }
  .feed-container-comments {
    .noComment {
      display: -webkit-flex;
      display: flex;
      justify-content: center;
      -webkit-justify-content: center;
      align-items: center;
      -webkit-align-items: center;
      background-color: #f4f5f5;
      img {
        padding: 5vh 0;
        width: 50%;
      }
    }
    .comments {
      .comments_counter {
        padding: 11px 5px;
        border-bottom: 3px #59b6d7 solid;
      }
    }
  }
  .feed-background-color {
    background-color: #fff;
  }
  .commentInput{
    padding: 8px 0px;
    width: 100%;
    z-index: 6;
    background-color: #fff;
    border-bottom: 1px #59b6d7 solid;
    border: none;
    textarea {
      min-height: 34px!important;
    }
  }
</style>
<style lang="less" module>
  .popup {
    width: 100%;
    background: rgba(0, 0, 0, 0);
    .popupButton {
      border-bottom: 1px solid #ededed;
      color: #333!important;
      border-radius: 0;
      font-size: 16px!important;
      &.deleteQuestion {
        color: #f00;
      }
      &:last-child {
        border-top: 5px solid #efefef;
      }
    }
  }
  .answerUser {
    padding-top: 12px;
    padding-bottom: 12px;
    background-color: #fff;
    border-bottom: 1px solid #ededed;
    .bio {
      text-align: initial;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      word-break: break-all;
      color: #999;
    }
  }
  .markdown-body {
    box-sizing: border-box;
    min-width: 200px;
    max-width: 980px;
    margin: 0 auto;
    padding: 45px;
  }

  @media (max-width: 767px) {
    .markdown-body {
      padding: 15px;
    }
  }
  .comment {
      padding: 0 12px;
      li {
        margin-top: 8px;
        &:last-child {
          margin-top: 0;
          margin-bottom: 8px;
        }
      }
      .commentOperations {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        .commentOperation {
          margin: 0 8px;
          &:last-child {
            margin-right: 0;
          }
        }
      }
    }
  .newsTitle {
    text-align: initial;
    overflow: hidden;
    text-overflow: ellipsis;  
    display: -webkit-box;  
    -webkit-line-clamp: 1;  
    -webkit-box-orient: vertical;
    word-break: break-all;
  }
  .followAction {
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }
  .perComment {
    padding: 10px 0;
    align-items: flex-start!important;
    .profileLink {
      color: #333;
    }
  }
  .wrapper{
    background-color: rgba(0, 0, 0, .3);
    z-index: 5;
    top: 0px;
    right: 0px;
    bottom: 0px;
    left: 0px;
    position: fixed;
    overflow: auto;
    margin: 0;
  }
  .sendComment {
    font-size: 14px;
    padding: 3px!important;
    background-color: #59b6d7;
    &[disabled] {
      background-color: #ccc!important;
      color: #fff!important;
    }
  }
  .commentContent{
    font-size: 14px;
    color: #999;
  }
  .operation {
    i {
      display: flex;
      display: -webkit-flex;
      -webkit-justify-content: center;
      -webkit-align-items: center;
      justify-content: center;
      align-items: center;
      font-style: normal;
    }
  }
</style>
