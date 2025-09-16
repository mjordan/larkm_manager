"""Management front end for larkm."""

import configparser
import json
import requests
import streamlit as st

# Read config file. This is not a Streamlit configuration file as described
# at https://docs.streamlit.io/develop/concepts/configuration/options, it's
# specific to this application.
config = configparser.ConfigParser()
config.sections()
config.read("config.cfg")
show_state_debug_info = config.getboolean("debug", "show_session_data")
verify_ssl_certs = config.getboolean("ssl", "verify_ssl_certs")
if verify_ssl_certs is False:
    requests.packages.urllib3.disable_warnings()


### Functions. ###


def get_larkm_config():
    """Returns a dict containing the larkm configuration."""
    headers = {"Authorization": config["larkm_host"]["api_key"]}
    result = requests.get(
        f'{config["larkm_host"]["host"]}/larkm/config',
        verify=verify_ssl_certs,
        headers=headers,
    )
    return json.loads(result.text)


def get_ark_data(ark_url):
    url = ark_url.replace("/ark:", "/larkm/ark:")
    result = requests.get(
        f"{url}",
        verify=verify_ssl_certs,
        headers={"Authorization": config["larkm_host"]["api_key"]},
    )
    st.session_state.get_ark_data_status_code = result.status_code
    return result


def get_ark():
    # Callback for the get_ark_data form submit.

    if st.session_state.ark_url.startswith("/"):
        st.session_state.ark_url = (
            config["larkm_host"]["host"].rstrip("/")
            + "/"
            + st.session_state.ark_url.lstrip("/")
        )
    if st.session_state.ark_url.startswith("ark:"):
        st.session_state.ark_url = (
            config["larkm_host"]["host"].rstrip("/") + "/" + st.session_state.ark_url
        )

    result = get_ark_data(st.session_state.ark_url)
    if result.status_code == 404:
        st.error(f"Oops! {st.session_state.ark_url} can't be found.")
        st.stop()
    else:
        st.session_state.ark_body = result.text


def create_ark():
    # Callback to create the ARK.

    payload = {
        "shoulder": st.session_state.shoulder,
        "target": st.session_state.ark_target_create,
        "who": st.session_state.erc_who_create,
        "what": st.session_state.erc_what_create,
        "when": st.session_state.erc_when_create,
    }
    if len(st.session_state.identifier_create) > 0:
        payload["identifier"] = st.session_state.identifier_create

    headers = {
        "content-type": "application/json",
        "Authorization": config["larkm_host"]["api_key"],
    }

    result = requests.post(
        f'{config["larkm_host"]["host"].rstrip("/")}/larkm',
        headers=headers,
        verify=verify_ssl_certs,
        json=payload,
    )
    if result.status_code == 201:
        ark = json.loads(result.text)

        st.success(f"ARK successfully created!")
        st.write(f'**ARK**: {ark["ark"]["ark_string"]}')
        st.write(f'**Target**: {ark["ark"]["target"]}')
        st.write(f'**What**: {ark["ark"]["what"]}')
        st.write(f'**Who**: {ark["ark"]["who"]}')
        st.write(f'**When**: {ark["ark"]["when"]}')
        st.write(
            f'**ARK URL** (suitable for sharing as a persistent URL): {config["larkm_host"]["host"].rstrip("/")}/{ark["ark"]["ark_string"]}'
        )
    else:
        st.error(
            f"POST (create ARK) request status code is {result.status_code} ({result.text})."
        )


def update_ark():
    """Callback to update the ARK with values from the form."""

    ark_body = json.loads(st.session_state.ark_body)

    payload = {
        "ark_string": ark_body["ark_string"],
        "target": st.session_state.ark_target_update,
        "who": st.session_state.erc_who_update,
        "what": st.session_state.erc_what_update,
        "when": st.session_state.erc_when_update,
    }

    headers = {
        "content-type": "application/json",
        "Authorization": config["larkm_host"]["api_key"],
    }
    url = st.session_state.ark_url.replace("/ark:", "/larkm/ark:")

    result = requests.put(
        url,
        headers=headers,
        verify=verify_ssl_certs,
        json=payload,
    )
    if result.status_code == 200:
        st.success(f"ARK {ark_body['ark_string']} successfully updated!")
    else:
        st.error(f"PUT (update ARK) request status code is {result.status_code}.")


def delete_ark():
    """Delete the ARK identified in the form."""

    headers = {
        "content-type": "application/json",
        "Authorization": config["larkm_host"]["api_key"],
    }
    if st.session_state.ark_url.startswith("http"):
        url = st.session_state.ark_url.replace("/ark:", "/larkm/ark:")
    else:
        url = f'{config["larkm_host"]["host"].rstrip("/")}/larkm/{st.session_state.ark_url}'

    result = requests.delete(
        url,
        headers=headers,
        verify=verify_ssl_certs,
    )
    if result.status_code == 204:
        st.success(f"ARK {st.session_state.ark_url} successfully deleted!")
    else:
        st.error(f"DELETE request status code is {result.status_code}.")


### Main program. ###

# Ping larkm.
try:
    get_larkm_config()
except Exception as e:
    st.error(f"Can't connect to {config['larkm_host']['host']}.")
    with st.expander("Details"):
        st.write(str(e))
    st.stop()


def create_page():
    # Render the create ARK form.

    st.session_state.identifier_create = ""
    st.session_state.ark_target_create = ""
    st.session_state.erc_who_create = ""
    st.session_state.erc_what_create = ""
    st.session_state.erc_when_create = ""

    with st.form("create_ark"):
        larkm_config = get_larkm_config()
        st.text_input(
            "Target (required)",
            help='The full https:// URL to the resource. In other words, the URL that the ARK is the "persistent URL" for.',
            key="ark_target_create",
        )
        st.text_input(
            "Identifier",
            help="Enter a UUIDv4 if you have one, otherwise larkm will generate one.",
            key="identifier_create",
        )
        st.selectbox(
            "Shoulder",
            list(larkm_config["allowed_shoulders"]),
            help=f'Do not change the default shoulder value unless you have consulted the [docs]({config["documentation"]["url"]}).',
            key="shoulder",
        )
        st.text_input("What", help="The title of the resource.", key="erc_what_create")
        st.text_input(
            "Who",
            help="A brief description of the creator of the resource. Free text.",
            key="erc_who_create",
        )
        st.text_input(
            "When",
            help="One or more dates associated with the resource. Free text.",
            key="erc_when_create",
        )
        st.form_submit_button("Create ARK", on_click=create_ark)


def update_page():
    # Render the update ARK form.

    with st.form("get_ark_data"):
        st.text_input(
            'Enter the ARK (starting with "ark:"), or the full larkm ARK URL.',
            key="ark_url",
        )
        st.form_submit_button("Get ARK data", on_click=get_ark)

    if len(st.session_state.ark_url) > 0:
        with st.form("update_ark"):
            ark_data = json.loads(st.session_state.ark_body)
            st.text(f'ARK: {ark_data["ark_string"]}')
            st.text(f"ARK URL: {st.session_state.ark_url}")
            st.text_input("Target", value=ark_data["target"], key="ark_target_update")
            st.text_input("What", value=ark_data["erc_what"], key="erc_what_update")
            st.text_input("Who", value=ark_data["erc_who"], key="erc_who_update")
            st.text_input("When", value=ark_data["erc_when"], key="erc_when_update")
            st.form_submit_button("Update ARK", on_click=update_ark)


def delete_page():
    # Render the delete ARK form.

    with st.form("delete_ark"):
        st.text_input(
            'Enter the ARK (starting with "ark:") you want to delete, or the full larkm ARK URL.',
            key="ark_url",
        )
        st.form_submit_button("Delete ARK", on_click=delete_ark)


pages = {
    "Create an ARK": [st.Page(create_page, title="Create an ARK")],
    "Update an ARK": [st.Page(update_page, title="Update an ARK")],
    "Delete an ARK": [st.Page(delete_page, title="Delete an ARK")],
}


if show_state_debug_info is True:
    with st.expander("Current session state at top of forms, for debugging"):
        st.write(st.session_state)


pg = st.navigation(pages)
pg.run()
