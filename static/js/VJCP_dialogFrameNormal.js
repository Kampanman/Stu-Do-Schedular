/**
 * コンポーネント：ダイアログフレーム（通常タイプ）
 */

let dialogFrameNormal = Vue.component('dialog-frame-normal', {
  template: `<div class="dialog">
    <v-app>
      <v-container>
        <v-row justify="center">
          <!-- persistentを指定するとモーダルになる -->
          <v-dialog v-model="target" max-width="500" persistent>
            <v-card>
              <v-card-title>
                <tag-title>{{ title }}</tag-title>
              </v-card-title><br /><br />
              <v-card-text>
                <p align="center">{{ contents }}</p>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                  <slot />
                <v-spacer></v-spacer>
              </v-card-actions><br />
            </v-card>
          </v-dialog>
        </v-row>
      </v-container>
    </v-app>
  </div>`,
  data: function () {
    return {
      //
    };
  },
  created: function () {
    this.init();
  },
  props: ['target', 'title', 'contents'],
  methods: {
    // 画面初期表示処理
    async init() {
      //
    },
  },
});

export default dialogFrameNormal;
