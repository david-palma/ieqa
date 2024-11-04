# Web application

The application software runs on a virtual Linux distribution (Ubuntu Server with a _properly configured_ LAMP web service solution stack) and is based on a software architectural pattern known as **Model-View-Controller** (MVC), implemented using the [CodeIgniter Web Framework](https://www.codeigniter.com/).
Consequently, a Controller, a Model, and several Views have been created, along with a database<sup>1</sup> to hold the information.

<sup>1</sup> _Current supported ones are MySQL (5.1+), MySQLi, Oracle, Postgres, MS SQL, SQLite, CUBRID, Interbase/Firebird and ODBC._

## Database

The database requirements are as follows:

- A table called _sensors_ which contains 6 columns: **id** (primary key and auto-incremented), **timestamp** (timestamp of the acquisition), **s0** (acquired value of temperature), **s1** (acquired value of relative humidity), **s2** (acquired value of brightness/luminosity), and **board** (ID of the board that has sent the data).

| Name          | Type    | Length | Default | Attributes | Auto increment | Comments              |
| ------------- | ------- | ------ | ------- | ---------- | -------------- | --------------------- |
| **id**        | INT     | 5      | None    | unsigned   | Yes            | primary key           |
| **timestamp** | INT     | 15     | None    | unsigned   | No             | time                  |
| **s0**        | DECIMAL | 6,3    | None    | unsigned   | No             | temperature [Celsius] |
| **s1**        | DECIMAL | 6,3    | None    | unsigned   | No             | relative humidity [%] |
| **s2**        | DECIMAL | 6,3    | None    | unsigned   | No             | luminosity [lux]      |
| **board**     | INT     | 1      | None    | unsigned   | No             | board id              |

- A table called _thresholds_ which contains 6 columns: **board** (unique ID of the board), **t0** (minimum temperature threshold value of the board), **t1** (maximum temperature threshold value of the board), **t2** (maximum relative humidity threshold value of the board), **t3** (minimum luminosity threshold value of the board), and **t4** (sampling time of the board).

| Name      | Type    | Length | Default | Attributes | Auto increment | Comments                   |
| --------- | ------- | ------ | ------- | ---------- | -------------- | -------------------------- |
| **board** | INT     | 1      | None    | unsigned   | No             | unique key                 |
| **t0**    | DECIMAL | 6,3    | None    | unsigned   | No             | min. temperature [Celsius] |
| **t1**    | DECIMAL | 6,3    | None    | unsigned   | No             | max. temperature [Celsius] |
| **t2**    | DECIMAL | 6,3    | None    | unsigned   | No             | max. relative humidity [%] |
| **t3**    | DECIMAL | 6,3    | None    | unsigned   | No             | min. luminosity [lux]      |
| **t4**    | INT     | 10     | None    | unsigned   | No             | sampling time              |

**NOTE**: Before implementing the model, it is necessary to inform the CodeIgniter Web Framework which database to use and how to access it by editing the relevant configuration file (according to the information used in the configuration setup of the LAMP web service).

## The Model-View-Controller (MVC) Pattern

The MVC pattern is a layered architecture implemented on the CodeIgniter web framework that divides a given software application into three interconnected parts, thereby separating the internal representations of information from the ways that information is presented to or accepted by the user. This separation allows each layer to focus solely on its role.

The three parts of the MVC are as follows:

1. **Model**: The first module captures the behavior of the application in terms of its problem domain, independent of the user interface. It directly manages the "knowledge," i.e., data, and the application's logic.
2. **View**: The second module implements the visual representation of the model (user interface), including the logic to display data to the user and handle user interaction with the application.
3. **Controller**: The last module serves as the (conceptual) link between the user and the system; it manages user input and instructs the views on how to respond to the user's requests.

<div align="center">
    <img src="../../figures/fig10.png" alt="MVC pattern." title="MVC pattern" width="300px;"/>
</div>

### Architecture

The following graphic illustrates how data flows throughout the system (**MVC implementation in CodeIgniter**):

- **index.php**: A generic (external) controller that initializes the resources needed by CodeIgniter (CI).
- **Router**: Examines the HTTP request and decides which controller should serve it. If the response to the current request is present in a cache, it will be immediately emitted.
- **Security Module**: Filters the HTTP request before sending it to the application controller.
- **Controller**: Loads the resources needed for processing the request and (possibly) instructs the model accordingly.
- **View**: Invoked to generate the content to be returned to the browser, which may be stored in the cache.

The overall framework is organized as follows (only directories):

| **application** | <span style="font-weight:normal">cache, config, controllers, core, errors, helpers, hooks, language, libraries, logs, models, views</span> |
| --------------: | :----------------------------------------------------------------------------------------------------------------------------------------- |
|      **assets** | css, fonts, js                                                                                                                             |
|      **system** | core, database, fonts, helpers, language, libraries                                                                                        |

while the MVC architecture is organized as follows:

|      **MODEL** | <span style="font-weight:normal">sensors.php</span>                                                                                                    |
| -------------: | :----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **CONTROLLER** | ieqa.php                                                                                                                                               |
|      **VIEWS** | analytics.php, dashboard.php, db_empty.php, error.php, footer.php, graphs.php, header.php, home.php, info.php, options.php, report.php, statistics.php |

<div align="center">
    <img src="../../figures/fig11.png" alt="CI architecture." title="CI architecture" width="6000px;"/>
</div>

### The Model

The model is where information can be retrieved, inserted, or updated in the database or other data stores. It is responsible for connecting to the database to perform Create, Read, Update, and Delete (CRUD) operations. Both the model and controller contain an uppercase constructor that bears the same name as the page itself. The model includes its own functions, which can be called as soon as a specific instance of the model has been created.

A list of the methods implemented in the model follows:

- `__construct()`: Constructor method that loads the database.
- `is_not_empty($table)`: Returns 1 if the query to the specified database table returns a number of entries greater than 0 (i.e., if it is not empty).
- `get_all()`: Queries the database to return all the information in the "sensors" table for both boards.
- `get_json_table($board)`: Similar to the above method, but returns the data in JSON format for a specific board.
- `get_json_graphs($board)`: Returns in JSON format all the information needed to implement graphs for a specific board.
- `get_json_statistics($par)`: Returns in JSON format all the statistics (minimum, maximum, expected value, variance, standard deviation, coefficient of variation, lower quartile, median, upper quartile, and interquartile range) for a specific sensor passed as a parameter for both boards.
- `get_thresholds($board)`: Returns in JSON format the threshold values stored in the database table `thresholds` for the selected board. If there is no entry in the table for the selected board, the method returns the default threshold values.
- `insert_data_into_db()`: Inserts data posted by the boards (i.e., temperature, relative humidity, luminosity, and timestamp) into the database for the selected board (using the HTTP POST method).
- `make_report($temp, $rh, $lux)`: Generates a report in HTML code with statistics, boxplots, network configuration, etc., by processing the JSON data of each sensor contained in the variables passed as parameters.
- `update_samp_time()`: Updates the sampling time in the database for the selected board (using the HTTP POST method). The method returns 1 if the sampling time of at least one board has been modified; otherwise, it returns 0.
- `update_threshold_values()`: Updates the threshold values in the database for the selected board (using the HTTP POST method). The method returns 1 if at least one threshold value of a board has been modified; otherwise, it returns 0.

### The Controller

The structure of the controller resembles that of the model, but the controller serves as the link between the back end and the front end. It calls methods from the model to output what needs to be displayed in the view. All database-related functions in CodeIgniter are set and called using the model, while the front end is displayed and managed by the view. In brief, the controller creates a new instance of the model, performs specific functions, and passes the results to the view for display in the web browser.

A list of the methods implemented in the controller follows:

- `__construct()`: Constructor method that loads the model, the URL Helper library (which contains functions that assist in working with URLs), and the Sessions library (which maintains a user's "state" and tracks their activity while browsing the website).
- `index()`: Loads the home page if the controller is invoked.
- `home()`: Loads the home view.
- `dashboard()`: Loads the dashboard view if the table is not empty<sup>2</sup>.
- `analytics()`: Loads the analytics view if the table is not empty<sup>2</sup>.
- `graphs()`: Loads the graphs view if the table is not empty<sup>2</sup>.
- `statistics()`: Loads the statistics view if the table is not empty<sup>2</sup>.
- `report()`: Loads the report view if the table is not empty<sup>2</sup>.
- `options()`: Loads the options view.
- `info()`: Loads the info view.
- `generate_report()`: Invokes the `get_json_statistics($par)` for each sensor and passes the returned JSON data to the `make_report($temp, $rh, $lux)` method in the model if the function is called through an HTTP POST request<sup>3</sup>.
- `json_data_table($board)`: Calls the `get_json_table($board)` method in the model for a specific board.
- `json_data_graphs($board)`: Calls the `get_json_graphs($board)` method in the model for a specific board.
- `json_data_statistics($par)`: Calls the `get_json_statistics($par)` method in the model for a specific sensor.
- `edit_samp_time()`: Invokes the `update_samp_time()` method in the model if the function is called through an HTTP POST request<sup>3</sup>.
- `edit_threshold_values()`: Invokes the `update_threshold_values()` method in the model if the function is called through an HTTP POST request<sup>3</sup>.
- `empty_table()`: Performs a shrink operation using a `TRUNCATE` statement on the `sensors` table, quickly removing all data from the table<sup>3</sup>.
- `data_available($board)`: Returns 0 if there are no data available in the `sensors` table for the selected board.
- `ping($host)`: Performs a ping request to test the reachability of a specific host (e.g., a board). The function returns 1 if the target host responds to the ICMP Echo Request with an ICMP Echo Reply; otherwise, it returns 0.
- `get_time()`: Returns the Unix timestamp of the server (used to synchronize the clocks between the board and server)<sup>4</sup>.
- `get_threshold_values($board)`: Returns the alarm threshold values and sampling time (used to update the values of the boards)<sup>4</sup>.
- `insert_data()`: Invokes the `insert_data_into_db()` method in the model if the function is called through an HTTP POST request<sup>3</sup>.

<sup>2</sup> _The function loads the proper view if the query from the model method `is_not_empty('sensors')` returns 1 (the table is not empty); otherwise, it loads the error view (no data available)._

<sup>3</sup> _The function invokes the proper method if the HTTP request method is POST; otherwise, it loads the error view (this method cannot be invoked directly)._

<sup>4</sup> _The function invokes the proper method if the HTTP request method is GET; otherwise, it loads the error view (this method cannot be invoked directly)._

### The Views

Views retrieve the data to display from the controller, which is responsible for fetching a particular view and passing the data to it as variables. A view resembles a regular HTML page with doctype, head, and body tags, or it can be a page fragment, like a header, footer, sidebar, etc., which can flexibly be embedded within other views.

A list of the main views follows:

- `dashboard()`: it uses charts and tables to illustrate the most important data;

<div align="center">
    <img src="../../figures/fig12.png" alt="Dashboard view." title="Dashboard view" width="5000px;"/>
</div>

- `graphs()`: it uses a graphic representation to perform time-series analysis;

<div align="center">
    <img src="../../figures/fig13.png" alt="Graphs view." title="Graphs view" width="5000px;"/>
</div>

- `statistics()`: it uses statistics and graphs (i.e., box-and-whisker plots) to make statistical analysis of data;

<div align="center">
    <img src="../../figures/fig14.png" alt="Dashboard view." title="Dashboard view" width="5000px;"/>
</div>

- `options()`: it allows a user to delete all the entries available in the database or to edit various options (e.g., threshold values and sampling rate) on a specific board.

<div align="center">
    <img src="../../figures/fig15.png" alt="Dashboard view." title="Dashboard view" width="5000px;"/>
</div>

## The mathematical model

Indoor environmental quality is one of the primary environmental health risks. To maintain the desired level of comfort, it is essential to perform indoor environmental quality monitoring in all buildings.

To estimate the environmental health of an indoor space, a model has been developed that combines the **Temperature-Humidity Index (THI)** and **Heat Index (HI)**, which are measures that account for the combined effects of environmental temperature and relative humidity to assess the degree of discomfort experienced by an individual, along with the **Luminosity-Brightness Index (LBI)** that provides an index based on the detected brightness.

### Temperature-Humidity Index (THI) and Heat Index (HI)

These two indices lead to important considerations for human comfort. When the body gets too hot, it begins to perspire to cool itself off. If perspiration cannot evaporate, the body cannot regulate its temperature. Evaporation is a cooling process; when perspiration evaporates off the body, it effectively reduces body temperature. When atmospheric moisture content (i.e., relative humidity) is high, the rate of perspiration decreases, making the body feel warmer. Conversely, when relative humidity decreases, perspiration increases, causing the body to feel cooler in arid conditions.

#### Temperature-Humidity Index calculation

The Temperature-Humidity Index measures the human body's reaction to a combination of heat and humidity [1]. It is calculated based on several formulas that consider air temperature and relative humidity. There are two approaches to calculate this index: "a-dimensionally" and in Celsius degrees.

$$\text{THI} = \left(T \cdot 1.8 + 32\right) - \left(0.55 - 0.0055 \cdot H\right) \cdot \left(\left(T \cdot 1.8 + 32\right) - 58 \right)$$

where $T$ is the air temperature in Celsius degrees and $H$ is the relative humidity in percentage.
If THI is:

- below 65 it means comfort state;
- between 65 and 80 it means alert state;
- above 80 it means discomfort state.

[1] E.C. Thom, "_The discomfort index_", Weatherwise, 12(2), pp. 57-61, 1959.

#### Heat Index calculation

There is a direct relationship between air temperature, relative humidity, and the heat index. As air temperature and relative humidity increase (or decrease), the heat index also increases (or decreases). The function that computes this is based on a multivariate fit to a model of the human body [1,2]. This equation approximates the heat index and reproduces the [NOAA](http://www.noaa.gov/) National Weather Service (NWS) table (with some exceptions). Given the ambient temperature in Fahrenheit and the relative humidity in percentage, the regression equation is:

$$\text{HI} = c_1 + c_2 T + c_3 H + c_4 T H + c_5 T^2 + c_6 H^2 + c_7 T^2 H + c_8 T H^2 + c_9 T^2 H^2$$

where $T$ is the ambient dry bulb temperature in Fahrenheit degrees and $H$ is the relative humidity in percentage.

The constants for this equation, which are within $\pm 1.7$°C of the NWS table for all humidities between 0 and 80% and all temperatures from 21 to 46°C, are:

- $c_1 =  0.363445176$,
- $c_2 =  0.988622465$,
- $c_3 =  4.777114035$,
- $c_4 = -0.114037667$,
- $c_5 = -0.000850208$,
- $c_6 = -0.020716198$,
- $c_7 =  0.000687678$,
- $c_8 =  0.000274954$,
- $c_9 =  0.000000000$.

[1] R.G. Steadman, "_The Assessment of Sultriness. Part I: A Temperature-Humidity Index Based on Human Physiology and Clothing Science_". Journal of Applied Meteorology and Climatology, 18(7), pp. 861-873, 1979.

[2] L.P. Rothfusz, "_The Heat Index Equation (or, More Than You Ever Wanted to Know About Heat Index)_", Scientific Services Division (NWS Southern Region Headquarters), 1990.

### Luminosity-Brightness Index (LBI)

This index calculates how different (in percentage) the observed values are from the reference set-point according to the existing legislation UNI EN 12464. This European Standard specifies lighting requirements in terms of quantity and quality of illumination for humans with normal visual capacity in indoor workplaces and their associated areas.

$$\text{LBI} = 1 - \dfrac{|\overline{L} - L_B|}{L_B} \cdot 100$$

For offices, it is recommended that light sources have a brightness of 500 lux.
