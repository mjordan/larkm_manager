A very basic GUI for the [larkm](https://github.com/mjordan/larkm) ARK manager/resolver that allows creating, editing, and deleting ARKs.

## Installation

Run `python -m pip install .`

## Usage

First, edit the configuration file, config.cfg, to point to your instance of larkm, and to define your NAAN and API key. Note that the IP address of the computer running larkm Manager must be registered in larkm's `trusted_ips` configuration setting.

If you want to locate your configuration file outside the larkm Manager directory, define the absolute path to the configuration file in your computer's `LARKM_MANAGER_CONFIG_FILE_PATH` environment variable.

To fire up larkm Manager, within the `larkm_manager` directory run `streamlit run larkm_manager.py`. You will be directed to the larkm Manager tab in your browser, where you will be able to create, edit, and delete ARKs.

## License

MIT


