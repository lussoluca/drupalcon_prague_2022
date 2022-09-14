import http from "k6/http";
import html from "k6/html";
import {sleep} from "k6";
import { randomString } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

export const options = {
  vus: 5,
  iterations: 50,
};

export default function () {
  const params = {
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
  };

  http.post(
    "https://drupal10.ddev.site/user/login",
    {
      ["name"]: "admin",
      ["pass"]: "admin",
      ["form_id"]: "user_login_form",
      ["op"]: "Log in",
    },
    params
  );

  sleep(10);

  const add_page = http.get(
    "https://drupal10.ddev.site/en/node/add/page",
  );

  http.post(
    "https://drupal10.ddev.site/en/node/add/page",
    {
      ["title[0][value]"]: randomString(8),
      ["form_id"]: "node_page_form",
      ["moderation_state[0][state]"]: "published",
      ["op"]: "Save",
      ["form_token"]: extractValue(add_page, 'input[name="form_token"]'),
    },
    params
  );

  sleep(10);

  http.get(
    "https://drupal10.ddev.site/en/microservice",
  );

  sleep(10);

  const add_article = http.get(
    "https://drupal10.ddev.site/en/node/add/article",
  );

  http.post(
    "https://drupal10.ddev.site/en/node/add/article",
    {
      ["title[0][value]"]: randomString(8),
      ["form_id"]: "node_article_form",
      ["moderation_state[0][state]"]: "published",
      ["op"]: "Save",
      ["form_token"]: extractValue(add_article, 'input[name="form_token"]'),
    },
    params
  );

  sleep(10);

  http.get(
    "https://drupal10.ddev.site/en/error",
  );

  sleep(10);

  http.get(
    "https://drupal10.ddev.site/en/user/logout",
  );
}

function extractValue(r, selector) {
  const doc = html.parseHTML(r.body);
  const matches = doc.find(selector);
  const match =
    matches.size() === 0
      ? null
      : matches.eq(Math.floor(Math.random() * matches.size()));
  const extract = match ? match.attr("value") : null;

  return extract || "ERROR";
}
